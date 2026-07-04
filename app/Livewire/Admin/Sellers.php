<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Store;
use Illuminate\Support\Facades\Auth;

class Sellers extends Component
{
    public string $view = 'list'; // 'list' or 'show'
    public ?int $storeId = null;
    public string $statusFilter = 'semua';

    public function filterSellers(string $status)
    {
        $this->statusFilter = $status;
    }

    public function showStore(int $id)
    {
        $this->storeId = $id;
        $this->view = 'show';
    }

    public function backToList()
    {
        $this->storeId = null;
        $this->view = 'list';
    }

    public function approveStore()
    {
        $store = Store::findOrFail($this->storeId);
        
        // 🔒 SECURITY FIX: Audit logging (ISSUE-013)
        \Log::info('Store approved by admin', [
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()->email,
            'store_id' => $store->id,
            'store_name' => $store->name,
            'owner_id' => $store->user_id,
            'owner_email' => $store->user->email,
            'timestamp' => now(),
            'ip_address' => request()->ip()
        ]);
        
        $store->update(['status' => 'approved']);
        
        $user = $store->user;
        $user->role = 'seller';
        $user->save();

        session()->flash('success', 'Toko ' . $store->name . ' telah disetujui.');
        $this->view = 'list';
        $this->storeId = null;
    }

    public function rejectStore()
    {
        $store = Store::findOrFail($this->storeId);
        
        // 🔒 SECURITY FIX: Audit logging (ISSUE-013)
        \Log::warning('Store rejected by admin', [
            'admin_id' => Auth::id(),
            'admin_email' => Auth::user()->email,
            'store_id' => $store->id,
            'store_name' => $store->name,
            'owner_id' => $store->user_id,
            'owner_email' => $store->user->email,
            'timestamp' => now(),
            'ip_address' => request()->ip()
        ]);
        
        $store->update(['status' => 'rejected']);

        session()->flash('success', 'Toko ' . $store->name . ' telah ditolak.');
        $this->view = 'list';
        $this->storeId = null;
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        if ($this->view === 'show' && $this->storeId) {
            $store = Store::with(['user', 'paymentMethods'])->findOrFail($this->storeId);
            return view('livewire.admin.sellers', [
                'store' => $store
            ])->extends('layouts.dashboard')->section('content');
        }

        $query = Store::with('user');

        if ($this->statusFilter !== 'semua') {
            $query->where('status', $this->statusFilter);
        }

        $stores = $query->orderBy('created_at', 'desc')->get();

        return view('livewire.admin.sellers', [
            'stores' => $stores
        ])->extends('layouts.dashboard')->section('content');
    }
}
