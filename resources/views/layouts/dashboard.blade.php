<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — UMKM Lampung Barat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface-100" x-data="{ sidebarOpen: false }" x-init="$data.toasts = []">

    {{-- Mobile Sidebar Overlay --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

    {{-- ============================================ --}}
    {{-- SIDEBAR --}}
    {{-- ============================================ --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-surface-200 transition-transform duration-300 lg:translate-x-0">
        {{-- Logo --}}
        <div class="h-16 flex items-center gap-2 px-5 border-b border-surface-100">
            <div class="w-8 h-8 rounded-lg hero-gradient flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8l-1.35-2.7A1 1 0 016.5 10H19m-3 3v6m-4-6v6m-4-6v6"/>
                </svg>
            </div>
            <span class="font-heading font-bold text-primary-500">UMKM <span class="text-accent-400">Lampung Barat</span></span>
        </div>

        {{-- Role Badge --}}
        <div class="px-5 py-3">
            @auth
                @if(Auth::user()->role === 'admin')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-primary-50 text-primary-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                        Admin
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-accent-50 text-accent-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-accent-500"></span>
                        Seller
                    </span>
                @endif
            @endauth
        </div>

        {{-- Navigation --}}
        <nav class="px-3 py-2 space-y-1 overflow-y-auto" style="max-height: calc(100vh - 140px);">
            @auth
                @if(Auth::user()->role === 'admin')
                    @php
                        $pendingSellersCount = \App\Models\Store::where('status', 'pending')->count();
                    @endphp
                    <x-sidebar-link href="/admin/dashboard" label="Dashboard" :active="Request::is('admin/dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>' />
                    <x-sidebar-link href="/admin/sellers" label="Verifikasi Seller" :badge="$pendingSellersCount > 0 ? $pendingSellersCount : null" :active="Request::is('admin/sellers*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35"/></svg>' />
                    <x-sidebar-link href="/admin/categories" label="Kategori" :active="Request::is('admin/categories*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>' />
                    <x-sidebar-link href="/admin/reports" label="Laporan Platform" :active="Request::is('admin/reports*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>' />
                @elseif(Auth::user()->role === 'seller' && Auth::user()->store)
                    @php
                        $store = Auth::user()->store;
                        $pendingOrdersCount = \App\Models\Order::where('store_id', $store->id)->where('status', 'waiting_payment')->count();
                    @endphp
                    <x-sidebar-link href="/seller/dashboard" label="Dashboard" :active="Request::is('seller/dashboard')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>' />
                    <x-sidebar-link href="/seller/products" label="Produk Saya" :active="Request::is('seller/products*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>' />
                    <x-sidebar-link href="/seller/orders" label="Pesanan" :badge="$pendingOrdersCount > 0 ? $pendingOrdersCount : null" :active="Request::is('seller/orders*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' />
                    <x-sidebar-link href="/seller/reports" label="Laporan" :active="Request::is('seller/reports*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>' />
                    <x-sidebar-link href="/seller/profile" label="Profil Toko" :active="Request::is('seller/profile*')" icon='<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>' />
                    <hr class="border-surface-100 my-3">
                    <a href="/stores/{{ $store->slug }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold rounded-xl text-surface-600 hover:bg-surface-50 transition-colors">
                        <svg class="w-5 h-5 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Lihat Toko
                    </a>
                @endif
            @endauth
        </nav>
    </aside>

    {{-- ============================================ --}}
    {{-- MAIN AREA --}}
    {{-- ============================================ --}}
    <div class="lg:ml-64 min-h-screen flex flex-col">
        {{-- Top Bar --}}
        <header class="sticky top-0 z-30 h-16 bg-white/90 backdrop-blur-sm border-b border-surface-200 flex items-center justify-between px-4 sm:px-6">
            {{-- Mobile Menu Toggle --}}
            <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-surface-100 transition-colors">
                <svg class="w-5 h-5 text-surface-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page Title --}}
            <h1 class="font-heading text-lg font-bold text-surface-900 hidden lg:block">@yield('page-title', 'Dashboard')</h1>

            {{-- Top Bar Actions --}}
            <div class="flex items-center gap-3">
                {{-- Notifications --}}
                @auth
                    @if(Auth::user()->role === 'seller' && Auth::user()->store)
                        @livewire('seller.notification-bell')
                    @else
                        <button class="relative p-2 rounded-lg hover:bg-surface-100 transition-colors">
                            <svg class="w-5 h-5 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </button>
                    @endif
                @endauth

                {{-- User Menu --}}
                @auth
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-surface-100 transition-colors">
                        <div class="w-8 h-8 rounded-full bg-primary-500 flex items-center justify-center">
                            <span class="text-sm font-semibold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-medium text-surface-800">
                                @if(Auth::user()->role === 'seller' && Auth::user()->store)
                                    {{ Auth::user()->store->name }}
                                @else
                                    {{ Auth::user()->name }}
                                @endif
                            </p>
                            <p class="text-xs text-surface-500">{{ Auth::user()->role === 'admin' ? 'Admin' : 'Seller' }}</p>
                        </div>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-card-hover border border-surface-200 py-2 z-50">
                        <a href="{{ url('/') }}" wire:navigate class="flex items-center gap-2 px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/></svg>
                            Ke Beranda
                        </a>
                        <hr class="my-1 border-surface-100">
                        <a href="{{ route('logout') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-danger hover:bg-red-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Keluar
                        </a>
                    </div>
                </div>
                @endauth
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    {{-- Toast Notifications --}}
    <div class="fixed bottom-4 right-4 z-[100] space-y-2" x-data="toastSystem" aria-live="polite">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="true" x-transition
                 :class="{ 'bg-green-600': toast.type === 'success', 'bg-red-600': toast.type === 'error', 'bg-blue-600': toast.type === 'info' }"
                 class="text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium min-w-[280px]">
                <span x-text="toast.message"></span>
            </div>
        </template>
    </div>

    @auth
        @if(Auth::user()->role === 'seller' && Auth::user()->store)
            <script>
                document.addEventListener('livewire:navigated', () => {
                    if (window.Echo) {
                        window.Echo.leave('stores.{{ Auth::user()->store->id }}');
                        window.Echo.private('stores.{{ Auth::user()->store->id }}')
                            .listen('OrderPaymentUploaded', (e) => {
                                // Play soft digital notification chime
                                const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-84.wav');
                                audio.play().catch(err => console.log('Audio playback error:', err));
                                
                                // Show toast notification
                                window.dispatchEvent(new CustomEvent('toast', {
                                    detail: {
                                        message: e.message + ' (Kode: ' + e.order_code + ') oleh ' + e.customer_name,
                                        type: 'info'
                                    }
                                }));

                                // Native browser push notification
                                if ('Notification' in window && Notification.permission === 'granted') {
                                    new Notification('UMKM Lampung Barat — Pesanan ' + e.order_code, {
                                        body: e.message + ' oleh ' + e.customer_name,
                                        icon: '/favicon.ico',
                                        tag: 'order-seller-' + e.order_id
                                    });
                                }
                                
                                // Refresh Livewire order list + notification bell
                                if (window.Livewire) {
                                    window.Livewire.dispatch('refresh-orders');
                                    window.Livewire.dispatch('notification-received');
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

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @stack('scripts')
</body>
</html>
