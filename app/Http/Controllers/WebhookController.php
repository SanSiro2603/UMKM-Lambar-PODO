<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\XenditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected XenditService $xendit;

    public function __construct(XenditService $xendit)
    {
        $this->xendit = $xendit;
    }

    /**
     * Handle callback webhook dari Xendit.
     * POST /api/webhook/xendit
     */
    public function handle(Request $request)
    {
        $callbackToken = $request->header('x-callback-token');

        if (! $callbackToken || ! $this->xendit->verifyWebhookToken($callbackToken)) {
            Log::warning('Webhook: invalid callback token', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        Log::info('Xendit webhook received', ['data' => $data]);

        $event     = $data['event'] ?? ($data['status'] ?? null);
        $invoiceId = $data['id'] ?? null;

        if (! $invoiceId) {
            return response()->json(['message' => 'Missing invoice id'], 400);
        }

        // Cari transaksi berdasarkan xendit_invoice_id
        $transaction = Transaction::with('order')
            ->where('xendit_invoice_id', $invoiceId)
            ->first();

        if (! $transaction) {
            Log::warning('Webhook: transaction not found', ['invoice_id' => $invoiceId]);
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $status = $data['status'] ?? $event;

        return match ($status) {
            'PAID'    => $this->handlePaid($transaction, $data),
            'EXPIRED' => $this->handleExpired($transaction),
            default   => response()->json(['message' => 'Status not handled'], 200),
        };
    }

    /**
     * Handle PAID: update order + transaksi → jalankan disbursement.
     */
    protected function handlePaid(Transaction $transaction, array $data): \Illuminate\Http\JsonResponse
    {
        try {
            DB::transaction(function () use ($transaction, $data) {
                $transaction->update([
                    'status'                 => 'paid',
                    'paid_at'                => now(),
                    'xendit_payment_method'  => $data['payment_method'] ?? $transaction->xendit_payment_method,
                    'xendit_payment_channel' => $data['payment_channel'] ?? $transaction->xendit_payment_channel,
                    'metadata'               => $data,
                ]);

                $transaction->order->update([
                    'payment_status' => 'paid',
                    'status'         => 'paid',
                    'paid_at'        => now(),
                ]);

                Log::info('Payment confirmed via webhook', [
                    'transaction_id' => $transaction->id,
                    'order_code'     => $transaction->order->order_code,
                ]);
            });

            // Jalankan disbursement (di luar DB transaction)
            $disbursement = $this->xendit->disbursementToSeller($transaction);

            if (! $disbursement['success']) {
                Log::error('Disbursement failed after paid webhook', [
                    'transaction_id' => $transaction->id,
                    'message'        => $disbursement['message'],
                ]);
            }

            return response()->json(['message' => 'Payment processed'], 200);
        } catch (\Throwable $e) {
            Log::error('Webhook PAID error', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Internal error'], 500);
        }
    }

    /**
     * Handle EXPIRED: update order & transaksi.
     */
    protected function handleExpired(Transaction $transaction): \Illuminate\Http\JsonResponse
    {
        DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status'     => 'expired',
                'expired_at' => now(),
            ]);

            $transaction->order->update([
                'payment_status' => 'failed',
                'status'         => 'cancelled',
            ]);

            Log::info('Payment expired via webhook', [
                'transaction_id' => $transaction->id,
                'order_code'     => $transaction->order->order_code,
            ]);
        });

        return response()->json(['message' => 'Payment expired processed'], 200);
    }
}