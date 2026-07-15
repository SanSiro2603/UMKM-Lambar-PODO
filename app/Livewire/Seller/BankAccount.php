<?php

namespace App\Livewire\Seller;

use App\Services\XenditService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class BankAccount extends Component
{
    protected XenditService $xendit;

    public string $bank_code = '';
    public string $bank_account_no = '';
    public string $bank_account_name = '';

    public function boot(XenditService $xendit): void
    {
        $this->xendit = $xendit;
    }

    public function mount(): void
    {
        $store = Auth::user()->store;

        if ($store) {
            $this->bank_code         = $store->bank_code ?? '';
            $this->bank_account_no   = $store->bank_account_no ?? '';
            $this->bank_account_name = $store->bank_account_name ?? '';
        }
    }

    /** Seller daftarkan / update rekening bank */
    public function save(): void
    {
        $user  = Auth::user();
        $store = $user->store;

        if (! $store) {
            session()->flash('error', 'Anda belum memiliki toko.');
            return;
        }

        $this->validate([
            'bank_code'         => ['required', 'string', 'max:20'],
            'bank_account_no'   => ['required', 'string', 'max:30', 'regex:/^\d+$/'],
            'bank_account_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s\.]+$/'],
        ], [
            'bank_code.required'         => 'Nama bank wajib dipilih.',
            'bank_account_no.required'   => 'Nomor rekening wajib diisi.',
            'bank_account_no.regex'      => 'Nomor rekening hanya boleh berisi angka.',
            'bank_account_name.required' => 'Nama pemilik rekening wajib diisi.',
            'bank_account_name.regex'    => 'Nama pemilik rekening hanya boleh berisi huruf, spasi, dan titik.',
        ]);

        // Validasi rekening ke Xendit API
        $validation = $this->xendit->validateBankAccount(
            $this->bank_code,
            $this->bank_account_no,
            $this->bank_account_name
        );

        if (! $validation['success']) {
            session()->flash('error', $validation['message']);
            return;
        }

        $store->update([
            'bank_name'         => $this->bank_code,
            'bank_code'         => $this->bank_code,
            'bank_account_no'   => $this->bank_account_no,
            'bank_account_name' => $this->bank_account_name,
            'bank_verify_status'=> 'pending',
            'bank_reject_reason'=> null,
        ]);

        Log::info('Seller registered bank account', [
            'store_id'  => $store->id,
            'bank_code' => $this->bank_code,
        ]);

        session()->flash('success', 'Rekening berhasil didaftarkan. Menunggu verifikasi admin.');
    }

    public function render(): View
    {
        $store = Auth::user()->store;
        $banks = config('banks.list');

        return view('livewire.seller.bank-account', compact('store', 'banks'));
    }
}
