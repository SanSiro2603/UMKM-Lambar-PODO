<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\Payout\PayoutApi;
use Xendit\Payout\CreatePayoutRequest;
use Xendit\Payout\DigitalPayoutChannelProperties;

class XenditService
{
    protected InvoiceApi $invoiceApi;
    protected PayoutApi $payoutApi;
    protected int $platformFeePercent;

    public function __construct()
    {
        $secretKey = config('services.xendit.secret_key');

        Configuration::setXenditKey($secretKey);

        $this->invoiceApi        = new InvoiceApi();
        $this->payoutApi         = new PayoutApi();
        $this->platformFeePercent = (int) config('services.xendit.platform_fee_percent', 5);
    }

    // ============================================================
    // VALIDASI REKENING BANK
    // ============================================================

    /**
     * Validasi kode bank via Xendit Payout Channels API.
     */
    public function validateBankAccount(string $bankCode, string $accountNo, string $accountName): array
    {
        try {
            $channels = $this->payoutApi->getPayoutChannels();

            $bank = collect($channels)->first(function ($ch) use ($bankCode) {
                return strtoupper($ch['channel_code']) === strtoupper($bankCode);
            });

            if (! $bank) {
                return ['success' => false, 'message' => 'Kode bank tidak ditemukan di Xendit.'];
            }

            Log::info('Bank validation passed', [
                'bank_code'   => $bankCode,
                'channel_name' => $bank['channel_name'] ?? $bankCode,
            ]);

            return [
                'success' => true,
                'data'    => ['bank_code' => $bankCode, 'bank_name' => $bank['channel_name'] ?? $bankCode],
            ];
        } catch (\Throwable $e) {
            Log::error('Xendit bank validation error', [
                'bank_code' => $bankCode,
                'error'     => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Gagal memvalidasi rekening: ' . $e->getMessage()];
        }
    }

    // ============================================================
    // INVOICE (Checkout Customer)
    // ============================================================

    /**
     * Buat invoice Xendit & dapatkan payment URL.
     */
    public function createInvoice(Order $order, Store $store, string $customerName, string $customerEmail): array
    {
        try {
            $externalId = 'INV-' . $order->order_code . '-' . time();

            // Biarkan Xendit menampilkan SEMUA metode bayar yang tersedia secara otomatis:
            // Virtual Account (BCA, BNI, BRI, Mandiri, BSI, Permata, CIMB, dll)
            // QRIS, E-Wallet (GoPay, OVO, DANA, ShopeePay, LinkAja), Gerai (Alfamart, Indomaret)

            $request = new CreateInvoiceRequest([
                'external_id'       => $externalId,
                'amount'            => (float) $order->total_price,
                'payer_email'       => $customerEmail,
                'description'       => "Pembayaran pesanan {$order->order_code} di {$store->name}",
                'invoice_duration'  => 86400,
                'currency'          => 'IDR',
                'success_redirect_url' => route('customer.orders.show', $order->id) . '?check=paid',
                'failure_redirect_url' => route('checkout'),
            ]);

            $invoice = $this->invoiceApi->createInvoice($request);

            Log::info('Xendit invoice created', [
                'order_code' => $order->order_code,
                'invoice_id' => $invoice['id'],
            ]);

            return [
                'success'          => true,
                'invoice_id'       => $invoice['id'],
                'payment_url'      => $invoice['invoice_url'],
                'status'           => $invoice['status'],
                'payment_method'   => $invoice['payment_method'] ?? null,
                'payment_channel'  => $invoice['payment_channel'] ?? null,
            ];
        } catch (\Throwable $e) {
            Log::error('Xendit create invoice error', [
                'order_code' => $order->order_code,
                'error'      => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Gagal membuat invoice: ' . $e->getMessage()];
        }
    }

    /**
     * Cek status invoice dari Xendit.
     */
    public function getInvoice(string $invoiceId): array
    {
        try {
            $invoice = $this->invoiceApi->getInvoiceById($invoiceId);
            return ['success' => true, 'data' => $invoice];
        } catch (\Throwable $e) {
            Log::error('Xendit get invoice error', [
                'invoice_id' => $invoiceId,
                'error'      => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Gagal mengecek status invoice.'];
        }
    }

    /** Kedaluwarsakan invoice yang nominalnya sudah tidak sesuai. */
    public function expireInvoice(string $invoiceId): array
    {
        try {
            $invoice = $this->invoiceApi->expireInvoice($invoiceId);

            return ['success' => true, 'data' => $invoice];
        } catch (\Throwable $e) {
            Log::error('Xendit expire invoice error', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Gagal memperbarui invoice pembayaran.'];
        }
    }

    // ============================================================
    // PAYOUT / DISBURSEMENT KE SELLER
    // ============================================================

    /**
     * Kirim dana ke rekening seller via Xendit Payout API.
     * SDK v6+: PayoutApi::createPayout() menggantikan DisbursementApi.
     * Saat XENDIT_PAYOUT_SIMULATION=true, langsung update transaksi tanpa panggil API.
     */
    public function disbursementToSeller(Transaction $transaction): array
    {
        try {
            $store = Store::where('user_id', $transaction->seller_id)->first();

            if (! $store) {
                return ['success' => false, 'message' => 'Data seller tidak ditemukan.'];
            }

            // Hitung platform fee & seller amount
            $totalAmount  = $transaction->total_amount;
            $platformFee  = (int) round($totalAmount * $this->platformFeePercent / 100);
            $sellerAmount = $totalAmount - $platformFee;

            $isSimulation = config('services.xendit.payout_simulation', false);

            if ($isSimulation) {
                // MODE SIMULASI: langsung update transaksi sebagai disbursed
                $transaction->update([
                    'xendit_disbursement_id' => 'SIM-DISB-' . $transaction->order->order_code . '-' . time(),
                    'platform_fee'           => $platformFee,
                    'seller_amount'          => $sellerAmount,
                    'status'                 => 'disbursed',
                    'disbursed_at'           => now(),
                ]);

                Log::info('Payout SIMULATION: dana langsung dicairkan', [
                    'transaction_id' => $transaction->id,
                    'seller_amount'  => $sellerAmount,
                    'platform_fee'   => $platformFee,
                    'store'          => $store->name,
                ]);

                return [
                    'success'         => true,
                    'disbursement_id' => 'SIM-DISB-' . $transaction->id,
                    'seller_amount'   => $sellerAmount,
                    'platform_fee'    => $platformFee,
                ];
            }

            // MODE PRODUKSI: panggil Xendit Payout API
            // Validasi kelengkapan data rekening
            if (empty($store->bank_code) || empty($store->bank_account_no) || empty($store->bank_account_name)) {
                Log::error('Disbursement failed: incomplete seller bank data', ['seller_id' => $store->id]);
                return ['success' => false, 'message' => 'Data rekening seller tidak lengkap.'];
            }

            $referenceId = 'DISB-' . $transaction->order->order_code . '-' . time();
            $idempotencyKey = $referenceId;

            // Channel properties (data rekening tujuan)
            $channelProperties = new DigitalPayoutChannelProperties([
                'account_holder_name' => $store->bank_account_name,
                'account_number'      => $store->bank_account_no,
            ]);

            // Create payout request
            $payoutRequest = new CreatePayoutRequest([
                'reference_id'       => $referenceId,
                'channel_code'       => strtoupper($store->bank_code),
                'channel_properties' => $channelProperties,
                'amount'             => (float) $sellerAmount,
                'description'        => "Pencairan pesanan {$transaction->order->order_code}",
                'currency'           => 'IDR',
            ]);

            $payout = $this->payoutApi->createPayout(
                $idempotencyKey,
                null,
                $payoutRequest
            );

            $transaction->update([
                'xendit_disbursement_id' => $payout['id'],
                'platform_fee'           => $platformFee,
                'seller_amount'          => $sellerAmount,
                'status'                 => 'disbursed',
                'disbursed_at'           => now(),
            ]);

            Log::info('Payout created successfully', [
                'transaction_id'  => $transaction->id,
                'payout_id'       => $payout['id'],
                'seller_amount'   => $sellerAmount,
                'platform_fee'    => $platformFee,
            ]);

            return [
                'success'          => true,
                'disbursement_id'  => $payout['id'],
                'seller_amount'    => $sellerAmount,
                'platform_fee'     => $platformFee,
            ];
        } catch (\Throwable $e) {
            Log::error('Xendit payout error', [
                'transaction_id' => $transaction->id,
                'error'          => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => 'Gagal payout: ' . $e->getMessage()];
        }
    }

    // ============================================================
    // WEBHOOK VERIFICATION
    // ============================================================

    /**
     * Validasi webhook token dari header x-callback-token.
     */
    public function verifyWebhookToken(string $token): bool
    {
        return hash_equals(config('services.xendit.webhook_token'), $token);
    }

    /**
     * Hitung platform fee.
     */
    public function calculatePlatformFee(int $totalAmount): int
    {
        return (int) round($totalAmount * $this->platformFeePercent / 100);
    }
}
