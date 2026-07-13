<div>
    <h2 class="font-heading text-2xl font-bold text-surface-900">Buka Toko Anda</h2>
    <p class="text-surface-500 mt-2">Daftarkan usaha Anda di platform UMKM Lampung Barat</p>

    {{-- Info Banner --}}
    <div class="mt-5 p-4 bg-accent-50 border border-accent-200 rounded-xl flex gap-3">
        <svg class="w-5 h-5 text-accent-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-accent-800">Setelah mendaftar, akun Anda akan diverifikasi oleh Admin dalam <strong>1x24 jam</strong>. Toko Anda akan tayang setelah disetujui.</p>
    </div>

    <form wire:submit.prevent="register" class="mt-6 space-y-4">
        {{-- Store Name --}}
        <div>
            <label for="store_name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Toko</label>
            <input id="store_name" type="text" placeholder="Contoh: Toko Kopi Pak Adi" wire:model.blur="store_name"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('store_name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Owner Name --}}
        <div>
            <label for="owner_name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Pemilik</label>
            <input id="owner_name" type="text" placeholder="Nama lengkap pemilik usaha" wire:model.blur="owner_name"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('owner_name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-surface-700 mb-1.5">Email</label>
            <input id="email" type="email" placeholder="email@usaha.com" wire:model.blur="email"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-surface-700 mb-1.5">No. Telepon / WhatsApp</label>
            <input id="phone" type="tel" placeholder="08xxxxxxxxxx" wire:model.blur="phone"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('phone') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Kecamatan --}}
        <div>
            <label for="districtCode" class="block text-sm font-medium text-surface-700 mb-1.5">Kecamatan (Kab. Lampung Barat)</label>
            <select id="districtCode" wire:model.live="districtCode"
                    class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <option value="">Pilih Kecamatan</option>
                @foreach($districts as $d)
                    <option value="{{ $d['code'] }}">{{ $d['name'] }}</option>
                @endforeach
            </select>
            @error('districtCode') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Desa/Kelurahan --}}
        <div>
            <label for="villageCode" class="block text-sm font-medium text-surface-700 mb-1.5">Desa / Kelurahan</label>
            <select id="villageCode" wire:model.live="villageCode"
                    class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <option value="">Pilih Desa/Kelurahan</option>
                @foreach($villages as $v)
                    <option value="{{ $v['code'] }}">{{ $v['name'] }}</option>
                @endforeach
            </select>
            @error('villageCode') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Detail Address --}}
        <div>
            <label for="detailAddress" class="block text-sm font-medium text-surface-700 mb-1.5">Alamat Toko / Usaha</label>
            <textarea id="detailAddress" rows="2" placeholder="Alamat lengkap toko: Jalan / RT/RW / No Rumah / Patokan" wire:model.blur="detailAddress"
                      class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            @error('detailAddress') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Description --}}
        <div>
            <label for="description" class="block text-sm font-medium text-surface-700 mb-1.5">Deskripsi Toko</label>
            <textarea id="description" rows="3" placeholder="Ceritakan tentang usaha Anda..." wire:model.blur="description"
                      class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
            @error('description') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Bank Info --}}
        <div class="p-4 bg-surface-50 rounded-xl space-y-4">
            <h4 class="font-semibold text-sm text-surface-800">Informasi Rekening Bank (Untuk Pencairan Dana)</h4>
            <p class="text-xs text-surface-500">Rekening ini digunakan untuk menerima hasil penjualan dari setiap transaksi yang berhasil. Masukkan data dengan benar.</p>
            <div>
                <label for="bank_code" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Bank</label>
                <select id="bank_code" wire:model.blur="bank_code" class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100">
                    <option value="">Pilih bank</option>
                    @foreach(config('banks.list') as $code => $name)
                        <option value="{{ $code }}">{{ $name }} ({{ $code }})</option>
                    @endforeach
                </select>
                @error('bank_code') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="bank_account_no" class="block text-sm font-medium text-surface-700 mb-1.5">Nomor Rekening</label>
                <input id="bank_account_no" type="text" placeholder="Masukkan nomor rekening (angka saja)" wire:model.blur="bank_account_no"
                       class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                @error('bank_account_no') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="bank_account_name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Pemilik Rekening</label>
                <input id="bank_account_name" type="text" placeholder="Sesuai nama di buku tabungan" wire:model.blur="bank_account_name"
                       class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                @error('bank_account_name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-surface-700 mb-1.5">Password</label>
            <input id="password" type="password" placeholder="Minimal 8 karakter" wire:model.blur="password"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('password') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Terms --}}
        <div>
            <label class="flex items-start gap-2 cursor-pointer">
                <input type="checkbox" wire:model="terms" class="w-4 h-4 rounded border-surface-300 text-primary-500 focus:ring-primary-200 mt-0.5">
                <span class="text-sm text-surface-600">Saya menyetujui <a href="#" class="text-primary-500 hover:underline">Syarat & Ketentuan Penjual</a></span>
            </label>
            @error('terms') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg text-sm flex items-center justify-center gap-2">
            <span wire:loading.remove wire:target="register">Daftar Sebagai Penjual</span>
            <span wire:loading wire:target="register" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span wire:loading wire:target="register">Memproses...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-surface-500">
        Sudah punya akun? <a href="{{ url('/login') }}" class="text-primary-500 font-semibold hover:underline">Masuk</a>
    </p>
</div>
