<div>
    <h2 class="font-heading text-2xl font-bold text-surface-900">Lupa Password?</h2>
    <p class="text-surface-500 mt-2">Masukkan email Anda dan kami akan mengirimkan link untuk mereset password.</p>

    @if($emailSent)
        <div class="mt-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
            Link reset password telah dikirim ke email Anda. Silakan cek inbox atau spam folder.
        </div>
        <div class="mt-6">
            <a href="{{ route('login') }}" wire:navigate class="text-sm text-primary-500 hover:text-primary-600 font-medium transition-colors">
                &larr; Kembali ke halaman masuk
            </a>
        </div>
    @else
        <form wire:submit.prevent="sendResetLink" class="mt-8 space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-surface-700 mb-1.5">Email</label>
                <input id="email" type="email" placeholder="nama@email.com" wire:model.blur="email"
                       class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                @error('email') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="w-full py-3 px-4 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg text-sm flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="sendResetLink">Kirim Link Reset</span>
                <span wire:loading wire:target="sendResetLink" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span wire:loading wire:target="sendResetLink">Mengirim...</span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" wire:navigate class="text-sm text-surface-500 hover:text-primary-500 transition-colors inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke halaman masuk
            </a>
        </div>
    @endif
</div>
