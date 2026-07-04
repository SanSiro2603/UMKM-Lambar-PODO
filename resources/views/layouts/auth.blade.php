<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Masuk') — UMKM Lampung Barat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface-50">
    <div class="min-h-screen flex">
        {{-- Left Side — Illustration / Branding --}}
        <div class="hidden lg:flex lg:w-1/2 hero-gradient relative overflow-hidden">
            {{-- Decorative Elements --}}
            <div class="absolute inset-0">
                <div class="absolute top-20 left-20 w-64 h-64 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 right-20 w-96 h-96 bg-accent-400/10 rounded-full blur-3xl"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] border border-white/5 rounded-full"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] border border-white/5 rounded-full"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] border border-white/5 rounded-full"></div>
            </div>

            {{-- Content --}}
            <div class="relative z-10 flex flex-col justify-center items-center text-center px-12">
                <div class="w-20 h-20 rounded-2xl gold-gradient flex items-center justify-center mb-8 shadow-lg">
                    <svg class="w-10 h-10 text-primary-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8l-1.35-2.7A1 1 0 016.5 10H19m-3 3v6m-4-6v6m-4-6v6"/>
                    </svg>
                </div>
                <h1 class="font-heading text-4xl font-bold text-white mb-4">UMKM Lampung Barat</h1>
                <p class="text-primary-200 text-lg max-w-md leading-relaxed">Platform marketplace terpercaya untuk memajukan produk-produk UMKM terbaik dari Lampung Barat.</p>

                {{-- Features List --}}
                <div class="mt-12 space-y-4 text-left">
                    <div class="flex items-center gap-3 text-white/90">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm">Buka toko online gratis & mudah</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/90">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm">Jangkau lebih banyak pelanggan</span>
                    </div>
                    <div class="flex items-center gap-3 text-white/90">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-sm">Kelola penjualan dari mana saja</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side — Form --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12">
            <div class="w-full max-w-md">
                {{-- Mobile Logo --}}
                <div class="lg:hidden flex items-center justify-center gap-2 mb-8">
                    <div class="w-10 h-10 rounded-lg hero-gradient flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8l-1.35-2.7A1 1 0 016.5 10H19m-3 3v6m-4-6v6m-4-6v6"/>
                        </svg>
                    </div>
                    <span class="font-heading font-bold text-xl text-primary-500">UMKM <span class="text-accent-400">Lampung Barat</span></span>
                </div>

                @yield('content')

                {{-- Back to Home --}}
                <div class="mt-8 text-center">
                    <a href="{{ url('/') }}" class="text-sm text-surface-500 hover:text-primary-500 transition-colors inline-flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
