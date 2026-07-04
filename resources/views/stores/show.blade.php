@extends('layouts.app')

@section('title', 'Toko Kopi Pak Adi')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Store Header --}}
    <div class="bg-white rounded-2xl overflow-hidden shadow-card mb-8">
        {{-- Banner --}}
        <div class="h-32 sm:h-48 hero-gradient relative">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-700/60 to-transparent"></div>
        </div>

        {{-- Store Info --}}
        <div class="px-6 pb-6 -mt-10 relative">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <div class="w-20 h-20 rounded-2xl bg-white shadow-card flex items-center justify-center border-4 border-white shrink-0">
                    <span class="font-heading font-bold text-3xl text-primary-500">T</span>
                </div>
                <div class="flex-1">
                    <h1 class="font-heading text-2xl font-bold text-surface-900">Toko Kopi Pak Adi</h1>
                    <p class="text-surface-500 text-sm mt-1">Kopi pilihan langsung dari kebun di Lampung Barat. Diproses secara tradisional untuk kualitas terbaik.</p>
                </div>
                <div class="flex gap-3 shrink-0">
                    <button class="px-4 py-2 text-sm font-medium text-primary-500 border border-primary-500 rounded-xl hover:bg-primary-50 transition-colors">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        Bagikan
                    </button>
                </div>
            </div>

            {{-- Stats --}}
            <div class="flex flex-wrap gap-6 mt-5 pt-5 border-t border-surface-100">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <span class="text-sm"><span class="font-bold text-surface-800">4.8</span> <span class="text-surface-500">Rating</span></span>
                </div>
                <div class="flex items-center gap-2 text-sm text-surface-500">
                    <svg class="w-5 h-5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span><span class="font-bold text-surface-800">12</span> Produk</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-surface-500">
                    <svg class="w-5 h-5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Lampung Barat
                </div>
                <div class="flex items-center gap-2 text-sm text-surface-500">
                    <svg class="w-5 h-5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Bergabung sejak Jan 2025
                </div>
            </div>
        </div>
    </div>

    {{-- Products --}}
    <h2 class="font-heading text-xl font-bold text-surface-900 mb-6">Semua Produk</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <x-product-card name="Kopi Robusta Lampung Barat Premium" price="75000" category="Minuman" seller="Toko Kopi Pak Adi" rating="4.8" sold="0" slug="kopi-robusta" sellerSlug="toko-kopi-pak-adi" />
        <x-product-card name="Kopi Liberika Lampung Barat" price="95000" category="Minuman" seller="Toko Kopi Pak Adi" rating="4.8" sold="0" slug="kopi-liberika" sellerSlug="toko-kopi-pak-adi" />
        <x-product-card name="Kopi Arabika Single Origin" price="110000" category="Minuman" seller="Toko Kopi Pak Adi" rating="4.9" sold="0" slug="kopi-arabika" sellerSlug="toko-kopi-pak-adi" />
        <x-product-card name="Kopi Sachet Instan 10pcs" price="25000" category="Minuman" seller="Toko Kopi Pak Adi" rating="4.5" sold="0" slug="kopi-sachet" sellerSlug="toko-kopi-pak-adi" />
        <x-product-card name="Drip Bag Coffee Box" price="85000" category="Minuman" seller="Toko Kopi Pak Adi" rating="4.7" sold="0" slug="drip-bag" sellerSlug="toko-kopi-pak-adi" />
        <x-product-card name="Cold Brew Concentrate 500ml" price="65000" category="Minuman" seller="Toko Kopi Pak Adi" rating="4.6" sold="0" slug="cold-brew" sellerSlug="toko-kopi-pak-adi" />
    </div>
</div>
@endsection
