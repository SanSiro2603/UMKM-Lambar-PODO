@extends('layouts.auth')

@section('title', 'Daftar Akun')

@section('content')
<div>
    <h2 class="font-heading text-2xl font-bold text-surface-900">Buat Akun Baru</h2>
    <p class="text-surface-500 mt-2">Daftar untuk mulai berbelanja di UMKM Lampung Barat</p>

    <form class="mt-8 space-y-4">
        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Lengkap</label>
            <input id="name" type="text" placeholder="Masukkan nama lengkap"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-surface-700 mb-1.5">Email</label>
            <input id="email" type="email" placeholder="nama@email.com"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-surface-700 mb-1.5">No. Telepon</label>
            <input id="phone" type="tel" placeholder="08xxxxxxxxxx"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Address --}}
        <div>
            <label for="address" class="block text-sm font-medium text-surface-700 mb-1.5">Alamat</label>
            <textarea id="address" rows="2" placeholder="Masukkan alamat lengkap"
                      class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-surface-700 mb-1.5">Password</label>
            <input id="password" type="password" placeholder="Minimal 8 karakter"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirm" class="block text-sm font-medium text-surface-700 mb-1.5">Konfirmasi Password</label>
            <input id="password_confirm" type="password" placeholder="Ulangi password"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Terms --}}
        <label class="flex items-start gap-2 cursor-pointer">
            <input type="checkbox" class="w-4 h-4 rounded border-surface-300 text-primary-500 focus:ring-primary-200 mt-0.5">
            <span class="text-sm text-surface-600">Saya menyetujui <a href="#" class="text-primary-500 hover:underline">Syarat & Ketentuan</a> dan <a href="#" class="text-primary-500 hover:underline">Kebijakan Privasi</a></span>
        </label>

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg text-sm">
            Daftar Sekarang
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-surface-500">
        Sudah punya akun? <a href="{{ url('/login') }}" class="text-primary-500 font-semibold hover:underline">Masuk</a>
    </p>
</div>
@endsection
