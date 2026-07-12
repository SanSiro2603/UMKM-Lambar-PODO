<?php

namespace App\Livewire\Seller;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Order;
use App\Events\OrderStatusUpdated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Orders extends Component
{
    public string $view = 'list';
    public ?int $orderId = null;
    public string $statusTab = 'semua';
    public string $inputShippingCost = '';
    public string $courierName = '';
    public string $courierPhone = '';
    public bool $editingCourier = false;

    public function mount($id = null)
    {
        if ($id) {
            $this->orderId = (int) $id;
            $this->view = 'show';
        }
    }

    #[On('refresh-orders')]
    public function refreshOrders(): void {}

    public function selectTab(string $tab): void
    {
        $this->statusTab = $tab;
    }

    public function showOrder(int $id): void
    {
        $this->orderId = $id;
        $this->view = 'show';
        $this->editingCourier = false;
    }

    public function backToList(): void
    {
        $this->orderId = null;
        $this->view = 'list';
        $this->editingCourier = false;
    }

    /** Seller input ongkir → status jadi waiting_payment */
    public function setShippingCost(): void
    {
        $this->validate([
            'inputShippingCost' => 'required|integer|min:0|max:1000000',
        ], [
            'inputShippingCost.required' => 'Ongkos kirim wajib diisi.',
            'inputShippingCost.integer'  => 'Ongkos kirim harus berupa angka.',
            'inputShippingCost.min'      => 'Ongkos kirim minimal Rp 0.',
            'inputShippingCost.max'      => 'Ongkos kirim maksimal Rp 1.000.000.',
        ]);

        $store = Auth::user()->store;
        $order = Order::where('store_id', $store->id)
                      ->where('id', $this->orderId)
                      ->firstOrFail();

        if ($order->shipping_cost !== null) {
            session()->flash('error', 'Ongkos kirim untuk pesanan ini sudah ditentukan.');
            return;
        }

        $cost = (int) $this->inputShippingCost;

        // Pesanan COD tidak memerlukan pembayaran online — langsung siap diproses/dikirim.
        $nextStatus = $order->payment_method === 'cod' ? 'paid' : 'waiting_payment';

        $order->update([
            'shipping_cost' => $cost,
            'total_price'   => $order->total_price + $cost,
            'status'        => $nextStatus,
        ]);

        $this->inputShippingCost = '';
        session()->flash('success', 'Ongkos kirim berhasil ditentukan: Rp ' . number_format($cost, 0, ',', '.'));
    }

    /** Seller input data kurir & kirim link pelacakan (token sekali pakai) via WhatsApp */
    public function sendCourierAccess(): void
    {
        $this->validateCourierInput();

        $store = Auth::user()->store;
        $order = Order::where('store_id', $store->id)
                      ->where('id', $this->orderId)
                      ->firstOrFail();

        if ($order->status !== 'paid') {
            session()->flash('error', 'Pesanan belum siap dikirim (menunggu pembayaran/ongkir).');
            return;
        }

        $this->issueCourierAccess($order, $store->name, ['status' => 'shipped']);
        event(new OrderStatusUpdated($order->fresh(), 'Pesanan Anda sedang dalam perjalanan!'));

        session()->flash('success', 'Akses kurir berhasil dibuat. Membuka WhatsApp...');
    }

    /** Seller buka form untuk mengoreksi nama/no. WA kurir (misal salah ketik) */
    public function editCourierAccess(): void
    {
        $store = Auth::user()->store;
        $order = Order::where('store_id', $store->id)
                      ->where('id', $this->orderId)
                      ->firstOrFail();

        if ($order->status !== 'shipped' || ! $order->courier_token) {
            session()->flash('error', 'Info kurir tidak dapat diubah untuk pesanan ini.');
            return;
        }

        $this->courierName = $order->courier_name ?? '';
        $this->courierPhone = $order->courier_phone ?? '';
        $this->editingCourier = true;
    }

    public function cancelEditCourier(): void
    {
        $this->editingCourier = false;
        $this->courierName = '';
        $this->courierPhone = '';
    }

    /** Simpan koreksi nama/no. WA kurir — link lama dihanguskan, link baru dikirim ulang */
    public function updateCourierAccess(): void
    {
        $this->validateCourierInput();

        $store = Auth::user()->store;
        $order = Order::where('store_id', $store->id)
                      ->where('id', $this->orderId)
                      ->firstOrFail();

        if ($order->status !== 'shipped' || ! $order->courier_token) {
            session()->flash('error', 'Info kurir tidak dapat diubah untuk pesanan ini.');
            return;
        }

        $this->issueCourierAccess($order, $store->name);
        $this->editingCourier = false;

        session()->flash('success', 'Info kurir diperbarui & link baru dikirim ulang via WhatsApp.');
    }

    private function validateCourierInput(): void
    {
        $this->validate([
            'courierName'  => 'required|string|max:100',
            'courierPhone' => 'required|string|min:9|max:15|regex:/^[0-9+]+$/',
        ], [
            'courierName.required'  => 'Nama kurir wajib diisi.',
            'courierPhone.required' => 'Nomor WhatsApp kurir wajib diisi.',
            'courierPhone.regex'    => 'Nomor WhatsApp hanya boleh berisi angka.',
        ]);
    }

    /** Generate token baru (menghanguskan link lama jika ada) & buka WhatsApp dengan link terbaru */
    private function issueCourierAccess(Order $order, string $storeName, array $extraFields = []): void
    {
        $token = Str::random(40);

        $order->update(array_merge([
            'courier_name'       => $this->courierName,
            'courier_phone'      => $this->courierPhone,
            'courier_token'      => $token,
            'is_tracking_active' => false,
            'courier_lat'        => null,
            'courier_lng'        => null,
        ], $extraFields));

        $trackingUrl = $order->fresh()->courierTrackingUrl();

        $message = "Halo {$this->courierName}, Anda ditugaskan mengantar pesanan #{$order->order_code} dari Toko {$storeName}.\n\n"
            . "Buka link berikut untuk melihat alamat tujuan & mulai pengantaran:\n{$trackingUrl}";

        if ($order->payment_method === 'cod') {
            $message .= "\n\n*Pesanan COD - tagih uang tunai Rp " . number_format($order->total_price, 0, ',', '.') . "*";
        }

        $waNumber = $this->formatPhoneForWhatsapp($this->courierPhone);
        $waUrl = "https://wa.me/{$waNumber}?text=" . rawurlencode($message);

        $this->courierName = '';
        $this->courierPhone = '';

        $this->dispatch('open-whatsapp', url: $waUrl);
    }

    private function formatPhoneForWhatsapp(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (!str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }

    /** Fallback manual — tandai selesai tanpa melalui halaman kurir */
    public function completeOrder(): void
    {
        $store = Auth::user()->store;
        $order = Order::where('store_id', $store->id)
                      ->where('id', $this->orderId)
                      ->firstOrFail();

        if ($order->status !== 'shipped') {
            session()->flash('error', 'Pesanan belum dikirim.');
            return;
        }

        $order->update([
            'status'             => 'delivered',
            'is_tracking_active' => false,
            'courier_token'      => null,
            'payment_status'     => $order->payment_method === 'cod' ? 'paid' : $order->payment_status,
            'paid_at'            => $order->payment_method === 'cod' ? now() : $order->paid_at,
        ]);

        session()->flash('success', 'Pesanan selesai.');
    }

    public function render()
    {
        $store = Auth::user()->store;
        if (! $store) abort(403);

        if ($this->view === 'show' && $this->orderId) {
            $order = Order::with(['customer', 'items.product', 'transaction'])
                ->where('store_id', $store->id)
                ->findOrFail($this->orderId);

            return view('livewire.seller.orders', compact('order', 'store'))
                ->extends('layouts.dashboard')->section('content');
        }

        $query = Order::with('customer')
            ->where('store_id', $store->id);

        if ($this->statusTab !== 'semua') {
            $query->where('status', $this->statusTab);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('livewire.seller.orders', compact('orders', 'store'))
            ->extends('layouts.dashboard')->section('content');
    }
}