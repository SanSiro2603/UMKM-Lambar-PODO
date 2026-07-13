<div>
    <h2 class="font-heading text-2xl font-bold text-surface-900">Buat Akun Baru</h2>
    <p class="text-surface-500 mt-2">Daftar untuk mulai berbelanja di UMKM Lampung Barat</p>

    <form wire:submit.prevent="register" class="mt-8 space-y-4">
        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Lengkap</label>
            <input id="name" type="text" placeholder="Masukkan nama lengkap" wire:model.blur="name"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('name') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-surface-700 mb-1.5">Email</label>
            <input id="email" type="email" placeholder="nama@email.com" wire:model.blur="email"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-surface-700 mb-1.5">No. Telepon</label>
            <input id="phone" type="tel" placeholder="08xxxxxxxxxx" wire:model.blur="phone"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('phone') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Kecamatan --}}
        <div>
            <label for="districtCode" class="block text-sm font-medium text-surface-700 mb-1.5">Kecamatan (Lampung Barat)</label>
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
            <label for="detailAddress" class="block text-sm font-medium text-surface-700 mb-1.5">Detail Alamat</label>
            <input id="detailAddress" type="text" placeholder="Jalan / RT/RW / No Rumah / Patokan" wire:model.blur="detailAddress"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('detailAddress') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-surface-700 mb-1.5">Password</label>
            <input id="password" type="password" placeholder="Minimal 8 karakter" wire:model.blur="password"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('password') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-surface-700 mb-1.5">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" placeholder="Ulangi password" wire:model.blur="password_confirmation"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Terms --}}
        <div>
            <label class="flex items-start gap-2 cursor-pointer">
                <input type="checkbox" wire:model="terms" class="w-4 h-4 rounded border-surface-300 text-primary-500 focus:ring-primary-200 mt-0.5">
                <span class="text-sm text-surface-600">Saya menyetujui <a href="#" class="text-primary-500 hover:underline">Syarat & Ketentuan</a> dan <a href="#" class="text-primary-500 hover:underline">Kebijakan Privasi</a></span>
            </label>
            @error('terms') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg text-sm flex items-center justify-center gap-2">
            <span wire:loading.remove wire:target="register">Daftar Sekarang</span>
            <span wire:loading wire:target="register" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span wire:loading wire:target="register">Memproses...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-surface-500">
        Sudah punya akun? <a href="{{ url('/login') }}" class="text-primary-500 font-semibold hover:underline">Masuk</a>
    </p>
</div>
