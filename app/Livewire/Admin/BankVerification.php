<?php

namespace App\Livewire\Admin;

use App\Models\Store;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class BankVerification extends Component
{
    use WithPagination;

    public string $rejectReason = '';
    public ?int $rejectingStoreId = null;
    public string $search = '';

    /** Admin approve rekening seller */
    public function approve(Store $store): void
    {
        $store->update([
            'bank_verify_status' => 'verified',
            'bank_reject_reason' => null,
        ]);

        Log::info('Admin approved seller bank account', ['store_id' => $store->id]);
        session()->flash('success', "Rekening <strong>{$store->name}</strong> berhasil diverifikasi.");
    }

    /** Tampilkan modal reject */
    public function showRejectModal(Store $store): void
    {
        $this->rejectingStoreId = $store->id;
        $this->rejectReason = '';
    }

    /** Proses reject rekening */
    public function reject(): void
    {
        if (empty(trim($this->rejectReason))) {
            session()->flash('error', 'Catatan wajib diisi saat menolak rekening.');
            $this->rejectingStoreId = null;
            return;
        }

        $store = Store::findOrFail($this->rejectingStoreId);

        $store->update([
            'bank_verify_status' => 'rejected',
            'bank_reject_reason' => $this->rejectReason,
        ]);

        Log::info('Admin rejected seller bank account', [
            'store_id' => $store->id,
            'reason'   => $this->rejectReason,
        ]);

        session()->flash('success', "Rekening <strong>{$store->name}</strong> ditolak.");
        $this->rejectingStoreId = null;
        $this->rejectReason = '';
    }

    public function cancelReject(): void
    {
        $this->rejectingStoreId = null;
        $this->rejectReason = '';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $stores = Store::with('user')
            ->whereNotNull('bank_account_no')
            ->when($this->search, fn ($q) => $q->where(fn ($sq) => $sq
                ->where('name', 'like', "%{$this->search}%")
                ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$this->search}%"))
            ))
            ->latest()
            ->paginate(20);

        return view('livewire.admin.bank-verification', compact('stores'))
            ->extends('layouts.dashboard')
            ->section('content');
    }
}