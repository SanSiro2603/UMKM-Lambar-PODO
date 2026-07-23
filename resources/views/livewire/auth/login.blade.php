<div>
    <h2 class="font-heading text-2xl font-bold text-surface-900">Selamat Datang Kembali!</h2>
    <p class="text-surface-500 mt-2">Masuk ke akun Anda untuk mulai berbelanja</p>

    @if (session()->has('seller_suspended'))
        @php($suspendedNotice = session('seller_suspended'))
        <div role="alert" class="mt-5 rounded-2xl border border-red-200 bg-red-50/80 p-4 text-red-900">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.667 1.73-3L13.73 4c-.77-1.333-2.69-1.333-3.46 0L3.34 16c-.77 1.333.19 3 1.73 3z"/>
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-heading text-base font-bold">{{ $suspendedNotice['title'] }}</h3>
                    <p class="mt-1 text-sm leading-relaxed text-red-700">Akses masuk ke akun ini telah diblokir oleh admin.</p>
                    <div class="mt-3 border-t border-red-200 pt-3">
                        <p class="text-xs font-semibold text-red-700">Alasan admin</p>
                        <p class="mt-1 whitespace-pre-line break-words text-sm leading-relaxed text-red-900">{{ $suspendedNotice['reason'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    @elseif (session()->has('error'))
        <div role="alert" class="mt-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="login" class="mt-8 space-y-5">
        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-surface-700 mb-1.5">Email</label>
            <input id="email" type="email" placeholder="nama@email.com" wire:model.blur="email"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Password --}}
        <div x-data="{ show: false }">
            <label for="password" class="block text-sm font-medium text-surface-700 mb-1.5">Password</label>
            <div class="relative">
                <input id="password" :type="show ? 'text' : 'password'" placeholder="Masukkan password" wire:model.blur="password"
                       class="w-full px-4 py-3 pr-10 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-surface-400 hover:text-surface-600">
                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="show" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @error('password') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
        </div>

        {{-- Remember & Forgot --}}
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="remember" class="w-4 h-4 rounded border-surface-300 text-primary-500 focus:ring-primary-200">
                <span class="text-sm text-surface-600">Ingat saya</span>
            </label>
            <a href="{{ route('password.request') }}" wire:navigate class="text-sm text-primary-500 hover:text-primary-600 font-medium transition-colors">Lupa password?</a>
        </div>

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg text-sm flex items-center justify-center gap-2">
            <span wire:loading.remove wire:target="login">Masuk</span>
            <span wire:loading wire:target="login" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span wire:loading wire:target="login">Memproses...</span>
        </button>
    </form>

    {{-- Divider --}}
    <div class="flex items-center gap-3 my-6">
        <div class="flex-1 h-px bg-surface-200"></div>
        <span class="text-xs text-surface-400 font-medium">ATAU</span>
        <div class="flex-1 h-px bg-surface-200"></div>
    </div>

    {{-- Register Links --}}
    <div class="space-y-3">
        <a href="{{ url('/register') }}" wire:navigate class="w-full flex items-center justify-center gap-2 py-3 px-4 border-2 border-surface-300 text-surface-700 font-semibold rounded-xl hover:border-primary-400 hover:text-primary-500 transition-all text-sm">
            Daftar Akun Baru
        </a>
        <a href="{{ url('/register-seller') }}" wire:navigate class="w-full flex items-center justify-center gap-2 py-3 px-4 border-2 border-accent-400 text-accent-600 font-semibold rounded-xl hover:bg-accent-50 transition-all text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35"/></svg>
            Daftar Sebagai Penjual
        </a>
    </div>
</div>
