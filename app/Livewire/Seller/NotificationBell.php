<?php

namespace App\Livewire\Seller;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    private function cacheKey(): string
    {
        return 'seller_notif_read_' . (Auth::user()->store?->id ?? 0);
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;

        if ($this->open) {
            // Catat waktu seller terakhir buka bell sebagai string ISO
            Cache::put($this->cacheKey(), now()->toIso8601String(), now()->addDays(30));
        }
    }

    public function close(): void
    {
        $this->open = false;
    }

    /** Dipanggil saat Echo menerima OrderPaymentUploaded (via Livewire.dispatch) */
    #[On('notification-received')]
    public function refresh(): void
    {
        // Tidak perlu isi — Livewire otomatis re-render setelah method dipanggil
    }

    public function render()
    {
        $store    = Auth::user()->store;
        $lastSeen = $store ? Cache::get($this->cacheKey()) : null;

        $notifications = collect();
        $unreadCount   = 0;

        if ($store) {
            $notifications = Order::where('store_id', $store->id)
                ->orderByDesc('created_at')
                ->limit(15)
                ->get(['id', 'order_code', 'status', 'total_price', 'created_at'])
                ->map(function ($order) use ($lastSeen) {
                    $order->is_new = $lastSeen
                        ? $order->created_at->gt($lastSeen)
                        : true;
                    return $order;
                });

            $countQuery = Order::where('store_id', $store->id);

            if ($lastSeen) {
                // Hitung order yang masuk SETELAH seller terakhir buka bell
                $countQuery->where('created_at', '>', $lastSeen);
            } else {
                // Belum pernah buka bell → semua order 30 hari terakhir = "baru"
                $countQuery->where('created_at', '>', now()->subDays(30));
            }

            $unreadCount = min($countQuery->count(), 99);
        }

        return view('livewire.seller.notification-bell', compact('notifications', 'unreadCount'));
    }
}
