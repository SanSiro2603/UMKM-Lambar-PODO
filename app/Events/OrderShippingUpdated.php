<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderShippingUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->order->customer_id),
            new PrivateChannel('stores.' . $this->order->store_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->id,
            'order_code' => $this->order->order_code,
            'customer_id' => $this->order->customer_id,
            'store_id' => $this->order->store_id,
            'shipping_address' => $this->order->shipping_address,
            'shipping_cost' => $this->order->shipping_cost,
            'shipping_zone_label' => $this->order->shipping_zone_label,
            'total_price' => $this->order->total_price,
            'message' => 'Alamat dan ongkir pesanan telah diperbarui.',
        ];
    }
}
