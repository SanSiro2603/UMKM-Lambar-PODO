@extends('layouts.dashboard')
@section('title', 'Manajemen Kategori')
@section('page-title', 'Manajemen Kategori')
@section('role-label', 'Admin')
@section('role-badge-class', 'bg-red-50 text-red-700')
@section('role-dot-class', 'bg-red-500')
@section('user-initial', 'A')
@section('user-name', 'Administrator')

@section('sidebar')
    <x-sidebar-link href="/admin/dashboard" label="Dashboard" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>' />
    <x-sidebar-link href="/admin/sellers" label="Verifikasi Seller" badge="3" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35"/></svg>' />
    <x-sidebar-link href="/admin/categories" label="Kategori" :active="true" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>' />
    <x-sidebar-link href="/admin/reports" label="Laporan Platform" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>' />
@endsection

@section('content')
    <div x-data="{ showModal: false }">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-surface-500">Kelola kategori produk untuk Public Storefront</p>
            <button @click="showModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-all text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Kategori
            </button>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-2xl shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-50 text-left">
                        <tr>
                            <th class="px-5 py-3 font-semibold text-surface-600">#</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Nama Kategori</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Jumlah Produk</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Dibuat</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-50">
                        @php
                            $categories = [
                                ['name' => 'Makanan Kering', 'count' => 45, 'created' => '15 Jan 2025'],
                                ['name' => 'Minuman', 'count' => 28, 'created' => '15 Jan 2025'],
                                ['name' => 'Pakaian', 'count' => 32, 'created' => '15 Jan 2025'],
                                ['name' => 'Kerajinan', 'count' => 24, 'created' => '20 Jan 2025'],
                                ['name' => 'Pertanian', 'count' => 18, 'created' => '20 Jan 2025'],
                                ['name' => 'Lainnya', 'count' => 12, 'created' => '1 Feb 2025'],
                            ];
                        @endphp
                        @foreach($categories as $i => $cat)
                            <tr class="hover:bg-surface-50/50 transition-colors">
                                <td class="px-5 py-4 text-surface-500">{{ $i + 1 }}</td>
                                <td class="px-5 py-4 font-medium text-surface-800">{{ $cat['name'] }}</td>
                                <td class="px-5 py-4 text-surface-600">{{ $cat['count'] }} produk</td>
                                <td class="px-5 py-4 text-surface-500">{{ $cat['created'] }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <button class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-500 transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 transition-colors" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Add Category Modal --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50" @click="showModal = false"></div>
            <div x-show="showModal" x-transition class="relative bg-white rounded-2xl shadow-modal p-6 max-w-md w-full">
                <h3 class="font-heading text-lg font-bold text-surface-900 mb-4">Tambah Kategori Baru</h3>
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1.5">Nama Kategori</label>
                        <input type="text" placeholder="Contoh: Makanan Basah"
                               class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-colors text-sm">Simpan</button>
                        <button type="button" @click="showModal = false" class="px-4 py-2.5 bg-surface-100 text-surface-600 font-medium rounded-xl hover:bg-surface-200 transition-colors text-sm">Batal</button>
                    </div>
                </form>
                <button @click="showModal = false" class="absolute top-4 right-4 text-surface-400 hover:text-surface-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>
@endsection
