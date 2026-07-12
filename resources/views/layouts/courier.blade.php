<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pelacakan Kurir') — UMKM Lampung Barat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface-50">
    <div class="max-w-lg mx-auto min-h-screen bg-white shadow-card">
        <header class="hero-gradient px-5 py-4 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-white/15 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 17h2l.64-2.54a2 2 0 00-1.94-2.46H17M6 17h12M6 17a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0zM3 10h11m0 0V6a1 1 0 011-1h3.28a1 1 0 01.948.684L20.28 10H14z"/></svg>
            </div>
            <span class="font-heading font-bold text-white text-sm">Halaman Kurir — UMKM Lampung Barat</span>
        </header>
        @yield('content')
    </div>
</body>
</html>
