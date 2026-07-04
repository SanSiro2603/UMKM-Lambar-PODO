@extends('layouts.dashboard')
@section('title', 'Detail Seller')
@section('page-title', 'Detail Seller')
@section('role-label', 'Admin')
@section('role-badge-class', 'bg-red-50 text-red-700')
@section('role-dot-class', 'bg-red-500')
@section('user-initial', 'A')
@section('user-name', 'Administrator')

@section('sidebar')
    <x-sidebar-link href="/admin/dashboard" label="Dashboard" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>' />
    <x-sidebar-link href="/admin/sellers" label="Verifikasi Seller" :active="true" badge="3" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35"/></svg>' />
    <x-sidebar-link href="/admin/categories" label="Kategori" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>' />
    <x-sidebar-link href="/admin/reports" label="Laporan Platform" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>' />
@endsection

@section('content')
    <a href="{{ url('/admin/sellers') }}" class="inline-flex items-center gap-1 text-sm text-surface-500 hover:text-primary-500 mb-4 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Store Info --}}
            <div class="bg-white rounded-2xl shadow-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-heading font-bold text-surface-900">Informasi Toko</h3>
                    <x-status-badge status="pending" />
                </div>
                <div class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-surface-500">Nama Toko:</span><p class="font-medium text-surface-800 mt-0.5">Kue Tradisional Bu Emi</p></div>
                    <div><span class="text-surface-500">Pemilik:</span><p class="font-medium text-surface-800 mt-0.5">Emiliana</p></div>
                    <div><span class="text-surface-500">Email:</span><p class="font-medium text-surface-800 mt-0.5">emiliana@email.com</p></div>
                    <div><span class="text-surface-500">Telepon:</span><p class="font-medium text-surface-800 mt-0.5">0813-5555-1234</p></div>
                    <div class="sm:col-span-2"><span class="text-surface-500">Alamat:</span><p class="font-medium text-surface-800 mt-0.5">Jl. Pasar Baru No. 12, Kabupaten Lampung Barat</p></div>
                    <div class="sm:col-span-2"><span class="text-surface-500">Deskripsi:</span><p class="font-medium text-surface-800 mt-0.5">Menjual berbagai produk UMKM khas Lampung Barat. Semua dibuat dari bahan alami dengan kualitas terbaik.</p></div>
                </div>
            </div>

            {{-- Bank Info --}}
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h3 class="font-heading font-bold text-surface-900 mb-4">Informasi Rekening</h3>
                <div class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><span class="text-surface-500">Bank:</span><p class="font-medium text-surface-800 mt-0.5">BRI</p></div>
                    <div><span class="text-surface-500">No. Rekening:</span><p class="font-medium text-surface-800 mt-0.5">5678 9012 3456 7890</p></div>
                    <div><span class="text-surface-500">Atas Nama:</span><p class="font-medium text-surface-800 mt-0.5">Emiliana</p></div>
                    <div><span class="text-surface-500">Tanggal Daftar:</span><p class="font-medium text-surface-800 mt-0.5">5 Juni 2025</p></div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div>
            <div class="bg-white rounded-2xl shadow-card p-5 sticky top-24">
                <h3 class="font-heading font-bold text-surface-900 mb-4">Tindakan</h3>
                <p class="text-sm text-surface-500 mb-4">Verifikasi data seller di atas. Pastikan informasi rekening dan data toko sudah benar.</p>
                <div class="space-y-3">
                    <button class="w-full py-3 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 transition-all hover:shadow-lg text-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Approve Seller
                    </button>
                    <button class="w-full py-3 bg-white border border-red-500 text-red-500 font-bold rounded-xl hover:bg-red-50 transition-colors text-sm">
                        Tolak Pendaftaran
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
