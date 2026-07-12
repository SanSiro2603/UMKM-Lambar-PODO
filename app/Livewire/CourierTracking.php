<?php

namespace App\Livewire;

use App\Events\CourierLocationUpdated;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Livewire\Component;

class CourierTracking extends Component
{
    public ?Order $order = null;
    public bool $invalid = false;
    public bool $trackingActive = false;
    public bool $justCompleted = false;

    public function mount(string $token): void
    {
        $order = Order::with(['customer', 'store'])
            ->where('courier_token', $token)
            ->where('status', 'shipped')
            ->first();

        if (! $order) {
            $this->invalid = true;
            return;
        }

        $this->order = $order;
        $this->trackingActive = (bool) $order->is_tracking_active;
    }

    /** Kurir klik "Mulai Antar" — mengaktifkan pengiriman lokasi berkala */
    public function startDelivery(): void
    {
        if (! $this->order || ! $this->order->courier_token || $this->order->status !== 'shipped') {
            return;
        }

        $this->order->update(['is_tracking_active' => true]);
        $this->trackingActive = true;
    }

    /** Dipanggil oleh browser kurir setiap 30 detik selama pengantaran berlangsung */
    public function updateLocation(float $lat, float $lng): void
    {
        if (! $this->order || ! $this->order->courier_token || $this->order->status !== 'shipped' || ! $this->order->is_tracking_active) {
            return;
        }

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return;
        }

        $this->order->update([
            'courier_lat' => $lat,
            'courier_lng' => $lng,
            'courier_location_updated_at' => now(),
        ]);

        event(new CourierLocationUpdated($this->order));
    }

    /** Kurir menyelesaikan pengantaran — token langsung dihanguskan (link sekali pakai) */
    public function completeDelivery(): void
    {
        if (! $this->order || ! $this->order->courier_token || $this->order->status !== 'shipped') {
            return;
        }

        $isCod = $this->order->payment_method === 'cod';

        $this->order->update([
            'status' => 'delivered',
            'is_tracking_active' => false,
            'courier_token' => null,
            'payment_status' => $isCod ? 'paid' : $this->order->payment_status,
            'paid_at' => $isCod ? now() : $this->order->paid_at,
        ]);

        $message = $isCod
            ? 'Pesanan Anda telah selesai diantar dan pembayaran COD telah diterima kurir!'
            : 'Pesanan Anda telah selesai diantar!';

        event(new OrderStatusUpdated($this->order, $message));

        $this->trackingActive = false;
        $this->justCompleted = true;
    }

    public function render()
    {
        return view('livewire.courier-tracking')->extends('layouts.courier')->section('content');
    }
}
