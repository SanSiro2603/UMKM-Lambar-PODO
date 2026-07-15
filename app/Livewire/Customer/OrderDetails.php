<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Transaction;
use App\Services\XenditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class OrderDetails extends Component
{
    public Order $order;

    // Rating state
    public array $ratingInputs = [];  // product_id => rating (1-5)
    public array $commentInputs = []; // product_id => comment text

    public function mount(int $id): void
    {
        $user = Auth::user();
        if (! $user || $user->role !== 'customer') abort(403);

        $this->order = Order::with(['store', 'items.product', 'transaction'])
            ->where('customer_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        // Inisialisasi rating inputs
        foreach ($this->order->items as $item) {
            $existing = Rating::query()->where('user_id', $user->id)
                ->where('product_id', $item->product_id)
                ->where('order_id', $this->order->id)
                ->first();

            if ($existing) {
                $this->ratingInputs[$item->product_id] = $existing->rating;
                $this->commentInputs[$item->product_id] = $existing->comment ?? '';
            } else {
                $this->ratingInputs[$item->product_id] = 0;
                $this->commentInputs[$item->product_id] = '';
            }
        }

        // Auto check pembayaran setelah redirect dari Xendit (check=paid)
        if (request()->query('check') === 'paid') {
            $this->checkPaymentStatus(app(XenditService::class));
        }
    }

    /** Cek apakah user sudah memberi rating untuk product tertentu di order ini */
    public function hasRated(int $productId): bool
    {
        return Rating::query()->where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->where('order_id', $this->order->id)
            ->exists();
    }

    /** Set rating sementara saat user klik bintang (realtime di client) */
    public function setRating(int $productId, int $value): void
    {
        $this->ratingInputs[$productId] = $value;
    }

    /** Submit rating + comment untuk satu product */
    public function submitRating(int $productId): void
    {
        $user = Auth::user();

        // Cek order status harus delivered
        if ($this->order->status !== 'delivered') {
            session()->flash('error', 'Pesanan belum selesai.');
            return;
        }

        // Cek product milik order ini
        $orderItem = $this->order->items->firstWhere('product_id', $productId);
        if (!$orderItem) {
            session()->flash('error', 'Produk tidak ditemukan dalam pesanan ini.');
            return;
        }

        // Cek sudah pernah rating
        if ($this->hasRated($productId)) {
            session()->flash('error', 'Anda sudah memberi rating untuk produk ini.');
            return;
        }

        $this->validate([
            "ratingInputs.{$productId}" => 'required|integer|min:1|max:5',
        ], [
            "ratingInputs.{$productId}.required" => 'Silakan pilih rating (1-5 bintang).',
            "ratingInputs.{$productId}.min" => 'Rating minimal 1 bintang.',
            "ratingInputs.{$productId}.max" => 'Rating maksimal 5 bintang.',
        ]);

        Rating::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'order_id' => $this->order->id,
            'rating' => (int) $this->ratingInputs[$productId],
            'comment' => trim($this->commentInputs[$productId] ?? '') ?: null,
        ]);

        // Update rating field di product (nilai rata-rata)
        $avg = round(Rating::query()->where('product_id', $productId)->avg('rating') ?? 0, 1);
        Product::query()->where('id', $productId)->update(['rating' => $avg]);

        session()->flash('success', 'Terima kasih! Rating berhasil dikirim.');
        $this->order->refresh();
    }

    // ========== Existing methods below ==========

    /** Customer klik bayar — buat invoice Xendit & redirect */
    public function payWithXendit(XenditService $xendit): mixed
    {
        if (! $this->order->canPay()) {
            session()->flash('error', 'Pesanan belum bisa dibayar. Pastikan ongkir sudah ditentukan.');
            return null;
        }

        // Cek transaksi pending
        $existing = Transaction::query()->where('order_id', $this->order->id)
            ->where('status', 'pending')
            ->first();

        if ($existing && $existing->xendit_invoice_url) {
            return redirect($existing->xendit_invoice_url);
        }

        $customer = Auth::user();

        $invoice = $xendit->createInvoice(
            $this->order,
            $this->order->store,
            $customer->name,
            $customer->email
        );

        if (! $invoice['success']) {
            Log::error('PayWithXendit: invoice creation failed', [
                'order_id' => $this->order->id,
                'message'  => $invoice['message'],
            ]);
            session()->flash('error', $invoice['message']);
            return null;
        }

        DB::transaction(function () use ($invoice) {
            Transaction::create([
                'order_id'              => $this->order->id,
                'seller_id'             => $this->order->store->user_id,
                'total_amount'          => $this->order->total_price,
                'xendit_invoice_id'     => $invoice['invoice_id'],
                'xendit_payment_method' => $invoice['payment_method'],
                'xendit_payment_channel'=> $invoice['payment_channel'],
                'xendit_invoice_url'    => $invoice['payment_url'],
                'status'                => 'pending',
            ]);

            $this->order->update([
                'xendit_invoice_id'  => $invoice['invoice_id'],
                'xendit_invoice_url' => $invoice['payment_url'],
            ]);
        });

        return redirect($invoice['payment_url']);
    }

    /** Manual check invoice status — fallback saat webhook Xendit tidak tembus localhost */
    public function checkPaymentStatus(XenditService $xendit): void
    {
        $transaction = Transaction::query()->where('order_id', $this->order->id)
            ->whereNotNull('xendit_invoice_id')
            ->latest()
            ->first();

        if (! $transaction || $transaction->status !== 'pending') {
            session()->flash('info', 'Tidak ada pembayaran yang perlu dicek.');
            return;
        }

        $result = $xendit->getInvoice($transaction->xendit_invoice_id);

        if (! $result['success']) {
            session()->flash('error', 'Gagal mengecek status. Coba lagi nanti.');
            return;
        }

        $status = $result['data']['status'] ?? 'PENDING';

        if ($status === 'PAID' || $status === 'SETTLED') {
            DB::transaction(function () use ($transaction, $result) {
                $transaction->update([
                    'status'                 => 'paid',
                    'paid_at'                => now(),
                    'xendit_payment_method'  => $result['data']['payment_method'] ?? null,
                    'xendit_payment_channel' => $result['data']['payment_channel'] ?? null,
                    'metadata'               => $result['data'],
                ]);

                $this->order->update([
                    'payment_status' => 'paid',
                    'status'         => 'paid',
                    'paid_at'        => now(),
                ]);
            });

            $disbursement = $xendit->disbursementToSeller($transaction);
            if (! $disbursement['success']) {
                Log::warning('Manual disbursement failed', [
                    'transaction_id' => $transaction->id,
                    'message'        => $disbursement['message'],
                ]);
            }

            session()->flash('success', 'Pembayaran berhasil dikonfirmasi!');
            $this->order->refresh();
        } elseif ($status === 'EXPIRED') {
            $transaction->update(['status' => 'expired', 'expired_at' => now()]);
            $this->order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
            session()->flash('error', 'Pembayaran sudah kadaluarsa.');
        } else {
            session()->flash('info', 'Status pembayaran: ' . $status . '. Silakan selesaikan pembayaran.');
        }
    }

    /** Batalkan order — hanya status waiting_payment */
    public function cancelOrder()
    {
        if ($this->order->status !== 'waiting_payment') {
            session()->flash('error', 'Pesanan tidak dapat dibatalkan.');
            return;
        }

        try {
            DB::transaction(function () {
                foreach ($this->order->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) $product->increment('stock', $item->qty);
                }

                $this->order->update([
                    'status'         => 'cancelled',
                    'payment_status' => 'failed',
                ]);
            });

            Log::info('Order cancelled by customer', [
                'order_id'   => $this->order->id,
                'order_code' => $this->order->order_code,
            ]);

            session()->flash('success', 'Pesanan berhasil dibatalkan.');
            $this->order->refresh();
        } catch (\Exception $e) {
            Log::error('Order cancellation failed', [
                'order_id' => $this->order->id,
                'error'    => $e->getMessage(),
            ]);
            session()->flash('error', 'Gagal membatalkan pesanan. Silakan coba lagi.');
        }
    }

    /** Customer konfirmasi terima → status delivered */
    public function confirmReceived()
    {
        if ($this->order->status !== 'shipped') {
            session()->flash('error', 'Pesanan belum dikirim.');
            return;
        }

        $this->order->update(['status' => 'delivered']);
        session()->flash('success', 'Pesanan selesai! Jangan lupa beri rating untuk produk yang Anda beli.');
        $this->order->refresh();
    }

    public function render()
    {
        return view('livewire.customer.order-details');
    }
}
