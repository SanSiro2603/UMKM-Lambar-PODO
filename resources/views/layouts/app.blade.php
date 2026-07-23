<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'UMKM Lampung Barat — Platform marketplace untuk produk UMKM terbaik dari Lampung Barat. Temukan berbagai produk lokal berkualitas.')">
    <meta name="keywords" content="UMKM, Lampung Barat, marketplace, produk lokal, belanja online">
    <title>@yield('title', 'UMKM Lampung Barat') — Marketplace UMKM Terpercaya</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes cart-pop {
            0% { transform: scale(1); }
            42% { transform: scale(1.18); }
            100% { transform: scale(1); }
        }
        .cart-pop {
            animation: cart-pop 0.4s cubic-bezier(0.22, 1, 0.36, 1);
        }
        @media (prefers-reduced-motion: reduce) {
            .cart-pop {
                animation: none;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col" x-data="toastSystem">

    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[120] focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-sm focus:font-bold focus:text-primary-700 focus:shadow-lg">
        Lewati ke konten utama
    </a>

    {{-- ============================================ --}}
    {{-- NAVBAR --}}
    {{-- ============================================ --}}
    <nav aria-label="Navigasi utama" class="sticky top-0 z-50 glass shadow-nav" x-data="{ mobileOpen: false, searchOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Top Bar --}}
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ url('/') }}" wire:navigate aria-label="UMKM Lampung Barat — Beranda" class="flex min-w-0 items-center gap-2 shrink-0">
                    <div class="w-9 h-9 rounded-lg hero-gradient flex items-center justify-center">
                        <svg aria-hidden="true" class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8l-1.35-2.7A1 1 0 016.5 10H19m-3 3v6m-4-6v6m-4-6v6"/>
                        </svg>
                    </div>
                    <span class="min-w-0 font-heading font-bold text-lg text-primary-500">
                        UMKM <span class="hidden min-[400px]:inline text-accent-400">Lampung Barat</span>
                    </span>
                </a>

                {{-- Search Bar (Desktop) --}}
                <form action="{{ route('products.index') }}" method="GET"
                      class="hidden md:flex flex-1 max-w-2xl mx-8"
                      x-data="{ catOpen: false, activeCat: 'Semua Kategori' }"
                      @keydown.escape.window="catOpen = false">
                    <div class="relative flex w-full border border-surface-300 bg-white/80 rounded-xl focus-within:border-primary-400 focus-within:ring-2 focus-within:ring-primary-100 focus-within:bg-white transition-all">
                        <!-- Category Selector Inside Search -->
                        <button @click="catOpen = !catOpen" type="button"
                                aria-haspopup="true" aria-controls="desktop-category-menu" :aria-expanded="catOpen.toString()"
                                class="flex min-h-11 items-center gap-1.5 px-4 text-xs font-semibold text-surface-600 hover:bg-surface-50 border-r border-surface-200 shrink-0 rounded-l-xl">
                            <span x-text="activeCat">Semua Kategori</span>
                            <svg aria-hidden="true" class="w-3.5 h-3.5 text-surface-400 transition-transform" :class="catOpen && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        
                        <!-- Dropdown Options -->
                        <div id="desktop-category-menu" x-show="catOpen" x-cloak @click.away="catOpen = false" x-transition
                             role="group" aria-label="Pilih kategori pencarian"
                             class="absolute left-0 top-full mt-2 w-64 max-h-72 overflow-y-auto bg-white rounded-xl shadow-card-hover border border-surface-200 py-1.5 z-50">
                            <button @click="activeCat = 'Semua Kategori'; catOpen = false" type="button" class="w-full text-left px-4 py-2 text-xs text-surface-700 hover:bg-surface-50 font-medium">
                                Semua Kategori
                            </button>
                            @foreach(\App\Models\Category::orderBy('name')->get() as $category)
                                <button @click="activeCat = @js($category->name); catOpen = false" type="button" class="w-full min-w-0 text-left px-4 py-2 text-xs text-surface-700 hover:bg-surface-50 font-medium">
                                    <span class="block truncate" title="{{ $category->name }}">{{ $category->name }}</span>
                                </button>
                            @endforeach
                        </div>

                        <input type="hidden" name="cat" :value="activeCat === 'Semua Kategori' ? '' : activeCat">

                        <!-- Text Input -->
                        <div class="relative flex-1">
                            <label for="desktop-product-search" class="sr-only">Cari produk atau toko</label>
                            <input id="desktop-product-search" name="q" type="search" placeholder="Cari produk atau toko..."
                                   class="w-full pl-10 pr-4 py-2.5 bg-transparent border-0 text-sm focus:ring-0 outline-none">
                            <svg aria-hidden="true" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </form>

                {{-- Nav Actions --}}
                <div class="flex shrink-0 items-center gap-1 min-[400px]:gap-2">
                    {{-- Search Toggle (Mobile) --}}
                    <button @click="searchOpen = !searchOpen" type="button"
                            aria-controls="mobile-search-panel" :aria-expanded="searchOpen.toString()"
                            :aria-label="searchOpen ? 'Tutup pencarian' : 'Buka pencarian'"
                            class="md:hidden min-w-11 min-h-11 inline-flex items-center justify-center rounded-xl hover:bg-surface-100 transition-colors">
                        <svg aria-hidden="true" class="w-5 h-5 text-surface-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    {{-- Cart (hanya untuk customer & guest) --}}
                    @if(!Auth::check() || Auth::user()->role === 'customer')
                        @livewire('cart-manager')
                    @endif

                    {{-- Auth Buttons --}}
                    @auth
                        {{-- Profile Dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @keydown.escape="open = false" type="button"
                                    aria-label="Buka menu akun" aria-controls="profile-menu" :aria-expanded="open.toString()"
                                    class="min-w-11 min-h-11 flex items-center justify-center gap-2 rounded-xl hover:bg-surface-100 transition-colors">
                                <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </div>
                                <svg class="w-4 h-4 text-surface-500 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="profile-menu" x-show="open" x-cloak @click.away="open = false" @keydown.escape.window="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-card-hover border border-surface-200 py-2 z-50">
                                @if(Auth::user()->role === 'admin')
                                    <a href="{{ url('/admin/dashboard') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 hover:bg-surface-50 font-medium">
                                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
                                        Dashboard Admin
                                    </a>
                                    <a href="{{ url('/admin/sellers') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">
                                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Verifikasi Seller
                                    </a>
                                    <a href="{{ url('/admin/categories') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">
                                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                                        Kelola Kategori
                                    </a>
                                @elseif(Auth::user()->role === 'seller')
                                    <a href="{{ url('/seller/dashboard') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 hover:bg-surface-50 font-medium">
                                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
                                        Dashboard Toko
                                    </a>
                                    <a href="{{ url('/seller/products') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">
                                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        Produk Saya
                                    </a>
                                    <a href="{{ url('/seller/orders') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">
                                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 11-4 0 2 2 0 014 0zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        Pesanan Masuk
                                    </a>
                                @else
                                    <a href="{{ url('/customer/dashboard') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 hover:bg-surface-50 font-medium">
                                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
                                        Dashboard Saya
                                    </a>
                                    <a href="{{ url('/customer/orders') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">
                                        <svg class="w-4 h-4 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        Pesanan Saya
                                    </a>
                                @endif
                                <hr class="my-1 border-surface-100">
                                <a href="{{ route('logout') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-danger hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Keluar
                                </a>
                            </div>
                        </div>
                    @else
                        <a href="{{ url('/login') }}" wire:navigate class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-primary-500 hover:bg-primary-50 rounded-lg transition-colors">
                            Masuk
                        </a>
                        <a href="{{ url('/register') }}" wire:navigate class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                            Daftar
                        </a>
                    @endif

                    {{-- Mobile Menu Toggle --}}
                    <button @click="mobileOpen = !mobileOpen" type="button"
                            aria-controls="mobile-navigation" :aria-expanded="mobileOpen.toString()"
                            :aria-label="mobileOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi'"
                            class="md:hidden min-w-11 min-h-11 inline-flex items-center justify-center rounded-xl hover:bg-surface-100 transition-colors">
                        <svg aria-hidden="true" x-show="!mobileOpen" class="w-5 h-5 text-surface-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg aria-hidden="true" x-show="mobileOpen" x-cloak class="w-5 h-5 text-surface-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Mobile Search --}}
            <form id="mobile-search-panel" x-show="searchOpen" x-cloak x-transition
                  action="{{ route('products.index') }}" method="GET" class="md:hidden pb-3">
                <label for="mobile-product-search" class="sr-only">Cari produk, toko, atau kategori</label>
                <input id="mobile-product-search" name="q" type="search" placeholder="Cari produk, toko, atau kategori..."
                       class="w-full min-h-11 pl-4 pr-4 py-2.5 rounded-xl border border-surface-300 bg-white text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100">
            </form>

            {{-- Main Navigation (Desktop) --}}
            <div class="hidden md:flex items-center justify-center gap-5 lg:gap-10 xl:gap-12 pb-3.5 text-sm lg:text-base border-t border-surface-100 pt-3.5 mt-1">
                <a href="{{ url('/') }}" wire:navigate @if(Request::is('/')) aria-current="page" @endif class="flex items-center gap-2 text-surface-600 hover:text-primary-500 font-semibold transition-colors {{ Request::is('/') ? 'text-primary-500 font-bold' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
                    Beranda
                </a>

                <a href="{{ route('panduan') }}" wire:navigate @if(Request::is('panduan')) aria-current="page" @endif class="flex items-center gap-2 text-surface-600 hover:text-primary-500 font-semibold transition-colors {{ Request::is('panduan') ? 'text-primary-500 font-bold' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"/></svg>
                    Panduan
                </a>
                
                <!-- Kategori Link -->
                <a href="{{ url('/products') }}" wire:navigate @if(Request::is('products*') && Request::has('cat')) aria-current="page" @endif class="flex items-center gap-2 text-surface-600 hover:text-primary-500 font-semibold transition-colors {{ Request::is('products*') && Request::has('cat') ? 'text-primary-500 font-bold' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    Kategori
                </a>

                <a href="{{ url('/stores') }}" wire:navigate @if(Request::is('stores*')) aria-current="page" @endif class="flex items-center gap-2 text-surface-600 hover:text-primary-500 font-semibold transition-colors {{ Request::is('stores*') ? 'text-primary-500 font-bold' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Toko UMKM
                </a>

                <a href="{{ url('/products') }}" wire:navigate @if(Request::is('products') && !Request::has('cat')) aria-current="page" @endif class="flex items-center gap-2 text-surface-600 hover:text-primary-500 font-semibold transition-colors {{ Request::is('products') && !Request::has('cat') ? 'text-primary-500 font-bold' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    Semua Produk
                </a>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-navigation" x-show="mobileOpen" x-cloak @keydown.escape.window="mobileOpen = false" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-surface-200 bg-white">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ url('/') }}" wire:navigate @if(Request::is('/')) aria-current="page" @endif class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Beranda</a>
                <a href="{{ route('panduan') }}" wire:navigate @if(Request::is('panduan')) aria-current="page" @endif class="block px-3 py-2 rounded-lg text-sm font-medium {{ Request::is('panduan') ? 'bg-primary-50 text-primary-600' : 'text-surface-700 hover:bg-surface-50' }}">Panduan</a>
                <a href="{{ url('/products') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Kategori</a>
                <a href="{{ url('/stores') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Toko UMKM</a>
                <a href="{{ url('/products') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Semua Produk</a>
                <hr class="border-surface-100 my-2">
                @auth
                    @if(Auth::user()->role === 'admin')
                        <a href="{{ url('/admin/dashboard') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Dashboard Admin</a>
                        <a href="{{ url('/admin/sellers') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Verifikasi Seller</a>
                        <a href="{{ url('/admin/categories') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Kelola Kategori</a>
                    @elseif(Auth::user()->role === 'seller')
                        <a href="{{ url('/seller/dashboard') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Dashboard Toko</a>
                        <a href="{{ url('/seller/products') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Produk Saya</a>
                        <a href="{{ url('/seller/orders') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Pesanan Masuk</a>
                    @else
                        <a href="{{ url('/customer/dashboard') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Dashboard Saya</a>
                        <a href="{{ url('/customer/orders') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-surface-700 hover:bg-surface-50">Pesanan Saya</a>
                    @endif
                    <a href="{{ route('logout') }}" class="block px-3 py-2 rounded-lg text-sm font-medium text-danger hover:bg-red-50">Keluar</a>
                @else
                    <a href="{{ url('/login') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-primary-500 hover:bg-primary-50">Masuk</a>
                    <a href="{{ url('/register') }}" wire:navigate class="block px-3 py-2 rounded-lg text-sm font-medium text-white bg-primary-500 text-center hover:bg-primary-600">Daftar</a>
                @endif
            </div>
        </div>
    </nav>

    {{-- ============================================ --}}
    {{-- MAIN CONTENT --}}
    {{-- ============================================ --}}
    <main id="main-content" class="flex-1" tabindex="-1">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>

    {{-- ============================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================ --}}
    <footer class="bg-primary-800 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                {{-- Brand --}}
                <div class="md:col-span-1">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 rounded-lg gold-gradient flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8l-1.35-2.7A1 1 0 016.5 10H19m-3 3v6m-4-6v6m-4-6v6"/>
                            </svg>
                        </div>
                        <span class="font-heading font-bold text-lg">UMKM <span class="text-accent-400">Lampung Barat</span></span>
                    </div>
                    <p class="text-primary-200 text-sm leading-relaxed">Platform marketplace terpercaya untuk produk-produk UMKM berkualitas dari Lampung Barat.</p>
                </div>

                {{-- Links --}}
                <div>
                    <h4 class="font-heading font-semibold text-accent-400 mb-4">Navigasi</h4>
                    <ul class="space-y-2 text-sm text-primary-200">
                        <li><a href="{{ url('/') }}" wire:navigate class="hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="{{ url('/products') }}" wire:navigate class="hover:text-white transition-colors">Produk</a></li>
                        <li><a href="{{ url('/register-seller') }}" wire:navigate class="hover:text-white transition-colors">Daftar Jadi Penjual</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-heading font-semibold text-accent-400 mb-4">Bantuan</h4>
                    <ul class="space-y-2 text-sm text-primary-200">
                        <li><a href="{{ route('panduan', ['tab' => 'customer']) }}" wire:navigate class="hover:text-white transition-colors">Cara Belanja</a></li>
                        <li><a href="{{ route('panduan', ['tab' => 'seller']) }}" wire:navigate class="hover:text-white transition-colors">Cara Menjual</a></li>
                        <li><a href="{{ route('panduan') }}" wire:navigate class="hover:text-white transition-colors">FAQ & Panduan</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-heading font-semibold text-accent-400 mb-4">Kontak</h4>
                    <ul class="space-y-2 text-sm text-primary-200">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Lampung Barat
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            info@umkmairhitam.id
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="border-primary-700 my-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-primary-300">
                <p>&copy; {{ date('Y') }} UMKM Lampung Barat. Hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    {{-- ============================================ --}}
    {{-- TOAST NOTIFICATIONS --}}
    {{-- ============================================ --}}
    <div class="fixed inset-x-4 bottom-4 z-[100] space-y-2 sm:left-auto sm:right-4 sm:w-auto" aria-label="Notifikasi">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                 :class="{
                     'bg-green-600': toast.type === 'success',
                     'bg-red-600': toast.type === 'error',
                     'bg-blue-600': toast.type === 'info',
                     'bg-amber-600': toast.type === 'warning'
                 }"
                 :role="toast.type === 'error' ? 'alert' : 'status'"
                 :aria-live="toast.type === 'error' ? 'assertive' : 'polite'"
                 aria-atomic="true"
                 class="w-full max-w-sm text-white pl-5 pr-2 py-2 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2 sm:min-w-[280px] motion-reduce:transition-none">
                <template x-if="toast.type === 'success'">
                    <svg aria-hidden="true" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </template>
                <span class="min-w-0 flex-1 break-words" x-text="toast.message"></span>
                <button type="button" @click="removeToast(toast.id)" aria-label="Tutup notifikasi"
                        class="min-w-9 min-h-9 inline-flex shrink-0 items-center justify-center rounded-lg text-white/90 hover:bg-white/15 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Login Modal (for Guest trying to add to cart) --}}
    <div x-data="loginModal" x-cloak
         role="dialog" aria-modal="true" aria-labelledby="login-dialog-title" aria-describedby="login-dialog-description"
         class="fixed inset-0 z-[90] flex items-center justify-center p-4 hidden">
        <button type="button" tabindex="-1" aria-hidden="true" class="absolute inset-0 bg-black/50 cursor-default" @click="close()"></button>
        <div class="relative bg-white rounded-2xl shadow-modal p-8 max-w-sm w-full text-center" @click.stop>
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-primary-50 flex items-center justify-center">
                <svg aria-hidden="true" class="w-8 h-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <h2 id="login-dialog-title" class="font-heading text-xl font-bold text-surface-900 mb-2">Masuk Terlebih Dahulu</h2>
            <p id="login-dialog-description" class="text-surface-500 text-sm mb-6">Silakan masuk atau daftar akun untuk mulai berbelanja di UMKM Lampung Barat.</p>
            <div class="flex flex-col gap-3">
                <a x-ref="primaryAction" href="{{ url('/login') }}" class="w-full py-2.5 px-4 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-colors">Masuk</a>
                <a href="{{ url('/register') }}" class="w-full py-2.5 px-4 border-2 border-primary-500 text-primary-500 font-semibold rounded-xl hover:bg-primary-50 transition-colors">Daftar Akun Baru</a>
            </div>
            <button type="button" @click="close()" aria-label="Tutup dialog masuk" class="absolute top-3 right-3 min-w-11 min-h-11 inline-flex items-center justify-center rounded-xl text-surface-400 hover:bg-surface-100 hover:text-surface-600">
                <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Real-Time Notification Listener (Customer Side) --}}
    @auth
        @if(Auth::user()->role === 'customer')
            <script>
                document.addEventListener('livewire:navigated', () => {
                    const userId = {{ Auth::id() }};
                    if (window.Echo) {
                        // Clean up previous listener to avoid duplicates
                        window.Echo.leave('users.' + userId);

                        window.Echo.private('users.' + userId)
                            .listen('OrderStatusUpdated', (e) => {
                                // Play notification chime
                                const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-84.wav');
                                audio.play().catch(err => console.log('Audio playback error:', err));

                                // Show in-app toast notification
                                window.dispatchEvent(new CustomEvent('toast', {
                                    detail: {
                                        message: e.message + ' (Kode: ' + e.order_code + ')',
                                        type: 'info'
                                    }
                                }));

                                // Native browser push notification
                                if ('Notification' in window && Notification.permission === 'granted') {
                                    new Notification('UMKM Lampung Barat — Pesanan ' + e.order_code, {
                                        body: e.message,
                                        icon: '/favicon.ico',
                                        tag: 'order-' + e.order_id
                                    });
                                }

                                // Refresh Livewire component if on order detail page
                                if (window.Livewire) {
                                    window.Livewire.dispatch('$refresh');
                                }
                            })
                            .listen('OrderShippingUpdated', (e) => {
                                window.dispatchEvent(new CustomEvent('toast', {
                                    detail: {
                                        message: e.message + ' (Kode: ' + e.order_code + ')',
                                        type: 'info'
                                    }
                                }));

                                if (window.Livewire) {
                                    window.Livewire.dispatch('order-shipping-updated', { orderId: e.order_id });
                                }
                            });
                    }
                });

                // Request browser notification permission on page load
                document.addEventListener('DOMContentLoaded', () => {
                    if ('Notification' in window && Notification.permission === 'default') {
                        Notification.requestPermission();
                    }
                });
            </script>
        @endif
    @endauth

    @stack('scripts')
</body>
</html>
