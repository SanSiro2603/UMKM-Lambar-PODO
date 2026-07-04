<?php

namespace App\Livewire\Seller;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class Orders extends Component
{
    public string $view = 'list';
    public ?int $orderId = null;
    public string $statusTab = 'semua';
    public string $inputShippingCost = '';

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
    }

    public function backToList(): void
    {
        $this->orderId = null;
        $this->view = 'list';
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

        $order->update([
            'shipping_cost' => $cost,
            'total_price'   => $order->total_price + $cost,
            'status'        => 'waiting_payment',
        ]);

        $this->inputShippingCost = '';
        session()->flash('success', 'Ongkos kirim berhasil ditentukan: Rp ' . number_format($cost, 0, ',', '.'));
    }

    /** Seller kirim barang → status shipped */
    public function shipOrder(): void
    {
        $store = Auth::user()->store;
        $order = Order::where('store_id', $store->id)
                      ->where('id', $this->orderId)
                      ->firstOrFail();

        if ($order->status !== 'paid') {
            session()->flash('error', 'Pesanan belum dibayar atau belum bisa dikirim.');
            return;
        }

        $order->update(['status' => 'shipped']);
        session()->flash('success', 'Pesanan telah dikirim ke kurir.');
    }

    /** Seller tandai selesai → status delivered */
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

        $order->update(['status' => 'delivered']);
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