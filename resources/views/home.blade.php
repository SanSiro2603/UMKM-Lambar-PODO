@extends('layouts.app')

@section('title', 'Beranda')
@section('meta_description', 'UMKM Lampung Barat — Temukan produk UMKM berkualitas dari Lampung Barat. Belanja mudah, aman, dan mendukung ekonomi lokal.')

@section('content')

{{-- ============================================ --}}
{{-- HERO SECTION --}}
{{-- ============================================ --}}
<section class="relative overflow-hidden bg-cover bg-center min-h-[420px] sm:min-h-[480px] lg:min-h-[520px]" style="background-image: url('{{ asset('images/air-hitam-hero.png') }}');">
    {{-- Image overlay --}}
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-r from-primary-900/90 via-primary-900/65 to-primary-900/20"></div>
        <div class="absolute inset-0 bg-primary-900/15 sm:bg-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-primary-900/45 to-transparent"></div>
        <svg aria-hidden="true" class="absolute bottom-0 left-0 right-0 text-surface-50" viewBox="0 0 1440 120" fill="currentColor" preserveAspectRatio="none">
            <path d="M0,96L48,90.7C96,85,192,75,288,74.7C384,75,480,85,576,90.7C672,96,768,96,864,85.3C960,75,1056,53,1152,48C1248,43,1344,53,1392,58.7L1440,64L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"/>
        </svg>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-20 sm:pt-20 sm:pb-36 lg:pt-24 lg:pb-40">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-sm font-medium text-white/90 ring-1 ring-white/15 backdrop-blur-sm mb-6">
                <span class="w-2 h-2 rounded-full bg-accent-400 shadow-[0_0_0_3px_rgba(212,168,67,0.18)]"></span>
                Marketplace UMKM Terpercaya
            </span>
            <h1 class="max-w-3xl font-heading text-[1.9rem] min-[360px]:text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-[1.08] tracking-[-0.025em]">
                Belanja Produk <span class="text-accent-400">UMKM Terbaik</span> dari Lampung Barat
            </h1>
            <p class="mt-5 text-primary-100 text-lg sm:text-xl leading-relaxed max-w-xl">
                Dukung ekonomi lokal dengan berbelanja langsung dari para pelaku UMKM. Produk berkualitas, harga bersahabat.
            </p>
            <div class="mt-8 flex flex-col items-stretch gap-3 min-[420px]:flex-row min-[420px]:items-center">
                <a href="{{ url('/products') }}" wire:navigate class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-accent-400 px-6 py-3 font-bold text-primary-900 shadow-[0_8px_24px_rgba(212,168,67,0.22)] transition-[transform,background-color,box-shadow] hover:-translate-y-0.5 hover:bg-accent-300 hover:shadow-[0_12px_30px_rgba(212,168,67,0.3)]">
                    <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Jelajahi Produk
                </a>
                <a href="{{ url('/register-seller') }}" wire:navigate class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-white/25 bg-white/10 px-6 py-3 font-semibold text-white backdrop-blur-sm transition-[background-color,border-color] hover:border-white/35 hover:bg-white/20">
                    <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72"/></svg>
                    Buka Toko Gratis
                </a>
            </div>
            {{-- Quick Stats --}}
            <div class="mt-10 grid max-w-sm grid-cols-3 divide-x divide-white/15">
                <div>
                    <p class="text-2xl font-heading font-bold text-white">150+</p>
                    <p class="text-sm text-primary-100">Produk</p>
                </div>
                <div class="pl-4 min-[360px]:pl-5 sm:pl-7">
                    <p class="text-2xl font-heading font-bold text-white">30+</p>
                    <p class="text-sm text-primary-100">Toko UMKM</p>
                </div>
                <div class="pl-4 min-[360px]:pl-5 sm:pl-7">
                    <p class="text-2xl font-heading font-bold text-white">500+</p>
                    <p class="text-sm text-primary-100">Transaksi</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- KATEGORI UNGGULAN --}}
{{-- ============================================ --}}
<section class="py-14 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 sm:mb-10">
            <h2 class="font-heading text-2xl sm:text-3xl font-bold text-surface-900">Jelajahi Kategori</h2>
            <p class="text-surface-600 mt-2">Temukan produk sesuai kebutuhanmu</p>
        </div>

        @php
            $categoryIcons = [
                'agrokimia' => 'flask',
                'pupuk-nutrisi' => 'leaf',
                'benih-bibit' => 'seedling',
                'perlengkapan-tani' => 'basket',
                'alat-spare-part-tani' => 'tools',
                'kopi' => 'coffee',
            ];
            $categoryColors = ['primary', 'accent', 'orange', 'primary', 'accent', 'orange'];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 md:grid-cols-6">
            @forelse($categories as $cat)
                <x-category-card
                    :name="$cat->name"
                    :icon="$categoryIcons[$cat->slug] ?? ($cat->icon ?? 'box')"
                    :count="$cat->products()->count()"
                    :href="url('/products?cat=' . $cat->slug)"
                    :color="$categoryColors[$loop->index % count($categoryColors)]"
                />
            @empty
                <div class="col-span-full rounded-2xl bg-white px-6 py-10 text-center shadow-card">
                    <svg aria-hidden="true" class="mx-auto h-10 w-10 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <h3 class="mt-4 font-heading text-lg font-bold text-surface-900">Kategori sedang disiapkan</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-surface-600">Pilihan kategori akan muncul setelah produk lokal tersedia.</p>
                    <a href="{{ route('products.index') }}" wire:navigate class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-primary-600">Lihat Semua Produk</a>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- PRODUK TERLARIS --}}
{{-- ============================================ --}}
<section class="py-14 bg-white sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8 sm:mb-10">
            <div>
                <h2 class="font-heading text-2xl sm:text-3xl font-bold text-surface-900">Produk Terlaris</h2>
                <p class="text-surface-600 mt-1">Pilihan favorit pembeli kami</p>
            </div>
            <a href="{{ url('/products') }}" wire:navigate class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-primary-500 hover:text-primary-600 transition-colors">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @forelse($products as $prod)
                <x-product-card
                    :name="$prod->name"
                    :price="$prod->price"
                    :category="$prod->category ? $prod->category->name : ''"
                    :seller="$prod->store ? $prod->store->name : ''"
                    :sold="$prod->sold_quantity"
                    :slug="$prod->slug"
                    :sellerSlug="$prod->store ? $prod->store->slug : ''"
                    :image="$prod->image ? asset('storage/' . $prod->image) : null"
                    :avgRating="round($prod->ratings_avg_rating ?? 0, 1)"
                    :ratingCount="$prod->ratings_count ?? 0"
                />
            @empty
                <div class="col-span-full rounded-2xl bg-surface-50 px-6 py-12 text-center">
                    <svg aria-hidden="true" class="mx-auto h-10 w-10 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <h3 class="mt-4 font-heading text-lg font-bold text-surface-900">Belum ada produk tersedia</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-surface-600">Produk dari UMKM lokal akan tampil di sini setelah siap dijual.</p>
                    <a href="{{ route('panduan', ['tab' => 'customer']) }}" wire:navigate class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl border border-primary-500 px-5 py-2.5 text-sm font-bold text-primary-600 hover:bg-primary-50">Pelajari Cara Belanja</a>
                </div>
            @endforelse
        </div>

        @if($products->isNotEmpty())
            <div class="sm:hidden mt-6 text-center">
                <a href="{{ url('/products') }}" wire:navigate class="inline-flex min-h-11 items-center gap-1 px-3 text-sm font-semibold text-primary-500">
                    Lihat Semua Produk
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        @endif
    </div>
</section>

{{-- ============================================ --}}
{{-- TOKO POPULER --}}
{{-- ============================================ --}}
<section class="py-14 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-8 sm:mb-10">
            <div>
                <h2 class="font-heading text-2xl sm:text-3xl font-bold text-surface-900">Toko Populer</h2>
                <p class="text-surface-600 mt-1">UMKM terpercaya pilihan kami</p>
            </div>
            <a href="{{ url('/stores') }}" wire:navigate class="inline-flex items-center gap-1 text-sm font-semibold text-primary-500 hover:text-primary-600 transition-colors">
                Lihat Semua
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($stores as $st)
                <x-store-card :name="$st->name" :slug="$st->slug" :description="$st->description" :products="$st->products()->count()" :banner="$st->banner ? asset('storage/' . $st->banner) : null" :logo="$st->logo ? asset('storage/' . $st->logo) : null" />
            @empty
                <div class="col-span-full rounded-2xl bg-white px-6 py-12 text-center shadow-card">
                    <svg aria-hidden="true" class="mx-auto h-10 w-10 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6M9 9h.01M12 9h.01M15 9h.01"/></svg>
                    <h3 class="mt-4 font-heading text-lg font-bold text-surface-900">Belum ada toko aktif</h3>
                    <p class="mx-auto mt-1 max-w-md text-sm text-surface-600">Toko yang telah diverifikasi akan tampil di bagian ini.</p>
                    <a href="{{ route('register.seller') }}" wire:navigate class="mt-5 inline-flex min-h-11 items-center justify-center rounded-xl bg-primary-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-primary-600">Daftar Sebagai Penjual</a>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- CTA — BUKA TOKO --}}
{{-- ============================================ --}}
<section class="py-14 bg-white sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-cover bg-center p-8 shadow-card-hover sm:rounded-3xl sm:p-12 lg:p-16" style="background-image: url('{{ asset('images/air-hitam-cta.png') }}');">
            {{-- Image overlay --}}
            <div class="absolute inset-0 bg-gradient-to-r from-primary-900/90 via-primary-900/65 to-primary-900/20"></div>
            <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-primary-900/40 to-transparent"></div>

            <div class="relative max-w-xl">
                <h2 class="font-heading text-3xl sm:text-4xl font-bold text-white leading-tight">
                    Punya Usaha? <span class="text-accent-400">Buka Toko</span> Sekarang!
                </h2>
                <p class="mt-4 text-primary-100 text-lg leading-relaxed">
                    Bergabunglah dengan ratusan UMKM lainnya di platform kami. Gratis, mudah, dan jangkau lebih banyak pelanggan.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="{{ url('/register-seller') }}" wire:navigate class="inline-flex min-h-12 items-center gap-2 rounded-xl bg-accent-400 px-6 py-3 font-bold text-primary-900 shadow-[0_8px_24px_rgba(212,168,67,0.2)] transition-[transform,background-color,box-shadow] hover:-translate-y-0.5 hover:bg-accent-300 hover:shadow-[0_12px_30px_rgba(212,168,67,0.28)]">
                        Daftar Sebagai Penjual
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
