@extends('layouts.auth')

@section('title', 'Daftar Sebagai Penjual')

@section('content')
<div>
    <h2 class="font-heading text-2xl font-bold text-surface-900">Buka Toko Anda</h2>
    <p class="text-surface-500 mt-2">Daftarkan usaha Anda di platform UMKM Lampung Barat</p>

    {{-- Info Banner --}}
    <div class="mt-5 p-4 bg-accent-50 border border-accent-200 rounded-xl flex gap-3">
        <svg class="w-5 h-5 text-accent-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-accent-800">Setelah mendaftar, akun Anda akan diverifikasi oleh Admin dalam <strong>1x24 jam</strong>. Toko Anda akan tayang setelah disetujui.</p>
    </div>

    <form class="mt-6 space-y-4">
        {{-- Store Name --}}
        <div>
            <label for="store_name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Toko</label>
            <input id="store_name" type="text" placeholder="Contoh: Toko Kopi Pak Adi"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Owner Name --}}
        <div>
            <label for="owner_name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Pemilik</label>
            <input id="owner_name" type="text" placeholder="Nama lengkap pemilik usaha"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-surface-700 mb-1.5">Email</label>
            <input id="email" type="email" placeholder="email@usaha.com"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Phone --}}
        <div>
            <label for="phone" class="block text-sm font-medium text-surface-700 mb-1.5">No. Telepon / WhatsApp</label>
            <input id="phone" type="tel" placeholder="08xxxxxxxxxx"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Address --}}
        <div>
            <label for="address" class="block text-sm font-medium text-surface-700 mb-1.5">Alamat Toko / Usaha</label>
            <textarea id="address" rows="2" placeholder="Alamat lengkap toko atau tempat usaha"
                      class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
        </div>

        {{-- Description --}}
        <div>
            <label for="description" class="block text-sm font-medium text-surface-700 mb-1.5">Deskripsi Toko</label>
            <textarea id="description" rows="3" placeholder="Ceritakan tentang usaha Anda..."
                      class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
        </div>

        {{-- Bank Info --}}
        <div class="p-4 bg-surface-50 rounded-xl space-y-4">
            <h4 class="font-semibold text-sm text-surface-800">Informasi Rekening / E-Wallet</h4>
            <div>
                <label for="bank_name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Bank / E-Wallet</label>
                <select id="bank_name" class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100">
                    <option value="">Pilih bank atau e-wallet</option>
                    <option>BRI</option>
                    <option>BNI</option>
                    <option>BCA</option>
                    <option>Mandiri</option>
                    <option>Bank Lampung</option>
                    <option>DANA</option>
                    <option>OVO</option>
                    <option>GoPay</option>
                </select>
            </div>
            <div>
                <label for="account_number" class="block text-sm font-medium text-surface-700 mb-1.5">Nomor Rekening / ID E-Wallet</label>
                <input id="account_number" type="text" placeholder="Masukkan nomor rekening"
                       class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
            <div>
                <label for="account_name" class="block text-sm font-medium text-surface-700 mb-1.5">Nama Pemilik Rekening</label>
                <input id="account_name" type="text" placeholder="Sesuai nama di rekening"
                       class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            </div>
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-surface-700 mb-1.5">Password</label>
            <input id="password" type="password" placeholder="Minimal 8 karakter"
                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
        </div>

        {{-- Terms --}}
        <label class="flex items-start gap-2 cursor-pointer">
            <input type="checkbox" class="w-4 h-4 rounded border-surface-300 text-primary-500 focus:ring-primary-200 mt-0.5">
            <span class="text-sm text-surface-600">Saya menyetujui <a href="#" class="text-primary-500 hover:underline">Syarat & Ketentuan Penjual</a></span>
        </label>

        {{-- Submit --}}
        <button type="submit" class="w-full py-3 px-4 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg text-sm">
            Daftar Sebagai Penjual
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-surface-500">
        Sudah punya akun? <a href="{{ url('/login') }}" class="text-primary-500 font-semibold hover:underline">Masuk</a>
    </p>
</div>
@endsection
