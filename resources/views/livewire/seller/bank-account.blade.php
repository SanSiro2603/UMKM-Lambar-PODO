<div>
    <h2 class="font-heading font-bold text-surface-900 text-xl mb-6">Pengaturan Rekening Bank</h2>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">{{ session('error') }}</div>
    @endif

    @php
        $status = $store->bank_verify_status ?? 'unverified';
        $sc = [
            'unverified' => ['bg' => 'bg-surface-100', 'text' => 'text-surface-600', 'label' => 'Belum Terdaftar'],
            'pending'    => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'label' => 'Menunggu Verifikasi'],
            'verified'   => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'label' => 'Terverifikasi'],
            'rejected'   => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'label' => 'Ditolak'],
        ];
        $s = $sc[$status] ?? $sc['unverified'];
    @endphp

    {{-- Status Rekening --}}
    <div class="bg-white rounded-2xl shadow-card p-5 mb-6">
        <h3 class="font-heading font-bold text-surface-900 mb-4">Status Rekening Bank</h3>
        <div class="flex items-center gap-3 mb-4">
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $s['bg'] }} {{ $s['text'] }}">{{ $s['label'] }}</span>
        </div>

        @if($status !== 'unverified')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><span class="text-surface-500">Nama Bank:</span> <span class="font-medium text-surface-800 ml-2">{{ config('banks.list')[$store->bank_code] ?? $store->bank_code }}</span></div>
            <div><span class="text-surface-500">Kode Bank:</span> <span class="font-medium text-surface-800 ml-2">{{ $store->bank_code }}</span></div>
            <div><span class="text-surface-500">Nomor Rekening:</span> <span class="font-medium text-surface-800 ml-2">{{ $store->bank_account_no }}</span></div>
            <div><span class="text-surface-500">Nama Pemilik:</span> <span class="font-medium text-surface-800 ml-2">{{ $store->bank_account_name }}</span></div>
        </div>
        @endif

        @if($status === 'rejected' && $store->bank_reject_reason)
        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
            <span class="text-sm text-red-700 font-medium">Alasan penolakan:</span>
            <p class="text-sm text-red-600 mt-1">{{ $store->bank_reject_reason }}</p>
        </div>
        @endif
    </div>

    {{-- Form Register / Update --}}
    <div class="bg-white rounded-2xl shadow-card p-5">
        <h3 class="font-heading font-bold text-surface-900 mb-1">{{ in_array($status, ['unverified', 'rejected']) ? 'Daftarkan Rekening Bank' : 'Perbarui Rekening Bank' }}</h3>
        <p class="text-sm text-surface-500 mb-4">Rekening ini digunakan untuk menerima pencairan dana hasil penjualan. Data akan diverifikasi admin.</p>

        <form wire:submit="save">
            <div class="mb-4">
                <label class="block text-sm font-medium text-surface-700 mb-1">Nama Bank</label>
                <select wire:model="bank_code" class="w-full px-3 py-2 border border-surface-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent @error('bank_code') border-red-300 @enderror">
                    <option value="">-- Pilih Bank --</option>
                    @foreach($banks as $code => $name)
                        <option value="{{ $code }}">{{ $name }} ({{ $code }})</option>
                    @endforeach
                </select>
                @error('bank_code') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-surface-700 mb-1">Nomor Rekening</label>
                <input type="text" wire:model="bank_account_no" placeholder="Contoh: 1234567890" class="w-full px-3 py-2 border border-surface-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent @error('bank_account_no') border-red-300 @enderror">
                @error('bank_account_no') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-surface-700 mb-1">Nama Pemilik Rekening</label>
                <input type="text" wire:model="bank_account_name" placeholder="Sesuai nama di buku tabungan" class="w-full px-3 py-2 border border-surface-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-400 focus:border-transparent @error('bank_account_name') border-red-300 @enderror">
                @error('bank_account_name') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-5 py-2 bg-primary-500 text-white rounded-xl text-sm font-semibold hover:bg-primary-600 transition-colors">
                    <span wire:loading.remove wire:target="save">{{ in_array($status, ['unverified', 'rejected']) ? 'Daftarkan Rekening' : 'Update Rekening' }}</span>
                    <span wire:loading wire:target="save" class="flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...</span>
                </button>
            </div>
        </form>
    </div>
</div>