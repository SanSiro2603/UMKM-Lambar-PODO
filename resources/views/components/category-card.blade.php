@props([
    'name' => 'Kategori',
    'icon' => 'box',
    'count' => 0,
    'href' => '#',
    'color' => 'primary',
])

@php
    $icons = [
        'food' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>',
        'drink' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
        'clothing' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>',
        'craft' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>',
        'farm' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>',
        'flask' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3h6m-5 0v6l-5.5 9.5A1.5 1.5 0 005.8 21h12.4a1.5 1.5 0 001.3-2.25L14 9V3M7 15h10"/>',
        'leaf' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 4C12 4 5 8 5 15c0 3 2 5 5 5 7 0 10-7 10-16zM5 19c3-4 7-7 12-10"/>',
        'seedling' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21v-9m0 0C9 12 6 10 5 6c4 0 7 2 7 6zm0 3c2-3 5-4 8-4-1 4-4 6-8 6"/>',
        'basket' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 10h14l-1 10H6L5 10zm3 0 4-6 4 6M9 13v4m3-4v4m3-4v4"/>',
        'tools' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 20l6-6m4-4 6-6M14 4l6 6M4 14l6 6"/>',
        'coffee' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h13v6a5 5 0 01-5 5H9a5 5 0 01-5-5V7zm13 2h1a3 3 0 010 6h-2M7 3v2m4-2v2m4-2v2"/>',
        'box' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
    ];
    $svgPath = $icons[$icon] ?? $icons['box'];

    $colors = [
        'primary' => 'bg-primary-50 text-primary-500 group-hover:bg-primary-100',
        'accent' => 'bg-accent-50 text-accent-600 group-hover:bg-accent-100',
        'blue' => 'bg-blue-50 text-blue-500 group-hover:bg-blue-100',
        'rose' => 'bg-rose-50 text-rose-500 group-hover:bg-rose-100',
        'purple' => 'bg-purple-50 text-purple-500 group-hover:bg-purple-100',
        'orange' => 'bg-orange-50 text-orange-500 group-hover:bg-orange-100',
    ];
    $colorClass = $colors[$color] ?? $colors['primary'];
@endphp

<a href="{{ $href }}" wire:navigate class="group relative min-w-0 flex min-h-40 flex-col items-center gap-3 overflow-hidden rounded-2xl bg-white p-4 text-center shadow-card card-hover sm:p-5">
    <div aria-hidden="true" class="absolute inset-x-5 top-0 h-px bg-gradient-to-r from-transparent via-primary-200 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
    <div class="w-14 h-14 rounded-xl {{ $colorClass }} flex items-center justify-center transition-colors ring-1 ring-current/5">
        <svg aria-hidden="true" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $svgPath !!}</svg>
    </div>
    <div class="min-w-0 w-full">
        <h3 class="min-h-10 break-words line-clamp-2 font-semibold text-sm text-surface-800 group-hover:text-primary-500 transition-colors">{{ $name }}</h3>
        @if($count > 0)
            <p class="text-xs text-surface-600 mt-0.5">{{ number_format($count, 0, ',', '.') }} produk</p>
        @endif
    </div>
</a>
