<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class Orders extends Component
{
    public string $statusTab = 'semua';

    public function selectTab(string $tab)
    {
        $this->statusTab = $tab;
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'customer') {
            abort(403);
        }

        $query = Order::with(['store', 'items.product'])
            ->where('customer_id', $user->id);

        if ($this->statusTab !== 'semua') {
            if ($this->statusTab === 'menunggu') {
                $query->where('status', 'waiting_payment');
            } else {
                $query->where('status', $this->statusTab);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20); // 🔒 SECURITY FIX: Add pagination (ISSUE-026)

        return view('livewire.customer.orders', [
            'orders' => $orders
        ])->extends('layouts.app')->section('content');
    }
}
