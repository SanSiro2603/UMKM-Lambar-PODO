<div class="max-w-4xl mx-auto py-6">
    {{-- Alerts --}}
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        
        {{-- Section 1: Visual Branding (Logo & Banner) --}}
        <div class="bg-white rounded-2xl shadow-card p-6">
            <h3 class="font-heading font-bold text-lg text-surface-900 mb-4 flex items-center gap-2 border-b border-surface-100 pb-3">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Media Branding Toko
            </h3>
            
            <div class="space-y-6">
                {{-- Banner Upload --}}
                <div>
                    <label class="block text-sm font-semibold text-surface-700 mb-2">Banner Toko (Rasio 16:9 atau 3:1 rekomendasi)</label>
                    <div class="relative rounded-2xl overflow-hidden group border-2 border-dashed border-surface-300 hover:border-primary-400 transition-colors h-48 bg-surface-50 flex items-center justify-center">
                        @if($newBanner)
                            <img src="{{ $newBanner->temporaryUrl() }}" class="w-full h-full object-cover">
                        @elseif($store->banner)
                            <img src="{{ asset('storage/' . $store->banner) }}" class="w-full h-full object-cover">
                        @else
                            <div class="text-center p-4">
                                <svg class="w-10 h-10 mx-auto mb-2 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-xs text-surface-600 font-medium">Klik atau seret file gambar untuk mengunggah banner toko</p>
                                <p class="text-[10px] text-surface-400 mt-1">Maksimal 2MB (Format: JPG, JPEG, PNG)</p>
                            </div>
                        @endif
                        <input type="file" wire:model="newBanner" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                    @error('newBanner') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Logo Upload & Basic Info Grid --}}
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    {{-- Logo Upload --}}
                    <div class="w-full md:w-1/3 flex flex-col items-center">
                        <label class="block text-sm font-semibold text-surface-700 mb-2 w-full text-left">Logo Toko</label>
                        <div class="relative w-36 h-36 rounded-2xl overflow-hidden border-2 border-dashed border-surface-300 hover:border-primary-400 transition-colors bg-surface-50 flex items-center justify-center group">
                            @if($newLogo)
                                <img src="{{ $newLogo->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif($store->logo)
                                <img src="{{ asset('storage/' . $store->logo) }}" class="w-full h-full object-cover">
                            @else
                                <div class="text-center p-3">
                                    <svg class="w-8 h-8 mx-auto mb-1 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <p class="text-[10px] text-surface-500 font-semibold leading-tight">Unggah Logo</p>
                                </div>
                            @endif
                            <input type="file" wire:model="newLogo" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        </div>
                        @error('newLogo') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Quick Identity Info --}}
                    <div class="flex-1 space-y-4 w-full">
                        <div>
                            <label for="name" class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1">Nama Toko</label>
                            <input type="text" id="name" wire:model="name" class="w-full px-4 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 bg-white">
                            @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            <p class="text-[10px] text-surface-400 mt-1 italic">*Mengubah nama toko akan mengubah link toko Anda (Slug: /stores/{{ Str::slug($name) }}).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Informasi Toko Detail --}}
        <div class="bg-white rounded-2xl shadow-card p-6">
            <h3 class="font-heading font-bold text-lg text-surface-900 mb-4 flex items-center gap-2 border-b border-surface-100 pb-3">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Informasi Toko & Alamat
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label for="description" class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1">Deskripsi Toko</label>
                    <textarea id="description" wire:model="description" rows="4" placeholder="Ceritakan tentang produk atau keunikan toko Anda..." class="w-full px-4 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 resize-none"></textarea>
                    @error('description') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="address" class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1">Alamat Lengkap Toko</label>
                    <textarea id="address" wire:model="address" rows="3" placeholder="Tulis alamat operasional toko untuk pengiriman..." class="w-full px-4 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 resize-none"></textarea>
                    @error('address') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- Section 3: Informasi Rekening Bank --}}
        <div class="bg-white rounded-2xl shadow-card p-6">
            <h3 class="font-heading font-bold text-lg text-surface-900 mb-4 flex items-center gap-2 border-b border-surface-100 pb-3">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Pembayaran & Rekening Pencairan
            </h3>

            <div class="space-y-3">
                <p class="text-sm text-surface-500">
                    Customer dapat membayar via <strong class="text-surface-700">Virtual Account bank (BCA, BNI, BRI, Mandiri, BSI, dll), QRIS, atau E-Wallet (GoPay, DANA, OVO, ShopeePay)</strong>.
                    Pembayaran dikonfirmasi otomatis dan dana diteruskan ke rekening Anda.
                </p>

                <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-800">Rekening Bank untuk Pencairan Dana</p>
                            <p class="text-xs text-blue-600 mt-1">Dana hasil penjualan (95%) akan otomatis dicairkan ke rekening bank Anda setelah pembayaran dikonfirmasi. Pastikan data rekening sudah benar dan sudah diverifikasi admin.</p>
                        </div>
                    </div>
                </div>

                @php
                    $bankStatus = $store->bank_verify_status ?? 'unverified';
                    $sc = [
                        'unverified' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Belum Terdaftar'],
                        'pending'    => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'label' => 'Menunggu Verifikasi'],
                        'verified'   => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'label' => 'Terverifikasi'],
                        'rejected'   => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'label' => 'Ditolak'],
                    ];
                    $s = $sc[$bankStatus] ?? $sc['unverified'];
                @endphp

                <div class="flex items-center justify-between p-4 bg-surface-50 rounded-xl">
                    <div>
                        <p class="text-sm font-semibold text-surface-800">
                            @if($store->bank_code)
                                {{ config('banks.list')[$store->bank_code] ?? $store->bank_code }} ({{ $store->bank_code }})
                            @else
                                Belum ada rekening terdaftar
                            @endif
                        </p>
                        @if($store->bank_account_no)
                            <p class="text-xs text-surface-500 mt-1">{{ $store->bank_account_no }} — a.n. {{ $store->bank_account_name }}</p>
                        @endif
                        <span class="inline-block mt-2 px-2 py-0.5 text-xs font-semibold rounded-full {{ $s['bg'] }} {{ $s['text'] }}">{{ $s['label'] }}</span>
                    </div>
                    <a href="{{ route('seller.bank.index') }}" class="px-4 py-2 bg-primary-500 text-white text-sm font-semibold rounded-xl hover:bg-primary-600 transition-colors">
                        {{ $bankStatus === 'unverified' || $bankStatus === 'rejected' ? 'Daftarkan Rekening' : 'Kelola Rekening' }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-bold rounded-xl transition-all shadow-md text-sm">
                <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                <span wire:loading wire:target="save" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
        
    </form>
</div>
