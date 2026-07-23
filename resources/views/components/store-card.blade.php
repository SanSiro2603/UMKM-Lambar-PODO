@props([
    'name' => 'Toko',
    'slug' => 'toko',
    'description' => '',
    'products' => 0,
    'avatar' => null,
    'banner' => null,
    'logo' => null,
])

<a href="{{ url('/stores/' . $slug) }}" wire:navigate class="group min-w-0 block h-full bg-white rounded-2xl overflow-hidden shadow-card card-hover">
    {{-- Banner --}}
    <div class="h-20 hero-gradient relative overflow-hidden">
        @if($banner)
            <img src="{{ $banner }}" alt="{{ $name }} Banner" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-primary-500/30 to-transparent"></div>
        @else
            <div class="absolute inset-0 bg-gradient-to-r from-primary-500/50 to-transparent"></div>
            <div aria-hidden="true" class="absolute right-5 top-1/2 h-16 w-16 -translate-y-1/2 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute right-9 top-1/2 h-9 w-9 -translate-y-1/2 rounded-full border border-white/10"></div>
        @endif
    </div>

    {{-- Info --}}
    <div class="px-5 pb-5 -mt-8 relative">
        {{-- Avatar --}}
        <div class="w-14 h-14 rounded-xl bg-white shadow-card flex items-center justify-center ring-4 ring-white mb-3 overflow-hidden">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $name }}" class="w-full h-full rounded-xl object-cover">
            @else
                <span class="font-heading font-bold text-xl text-primary-500">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
            @endif
        </div>

        <h3 class="break-words line-clamp-2 font-heading font-bold text-surface-800 group-hover:text-primary-500 transition-colors">{{ $name }}</h3>

        @if($description)
            <p class="break-words text-xs text-surface-600 mt-1 line-clamp-2">{{ $description }}</p>
        @endif

        <div class="flex items-center gap-3 mt-3 text-xs text-surface-600">
            <div class="flex items-center gap-1">
                <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>{{ number_format($products, 0, ',', '.') }} produk</span>
            </div>
        </div>
    </div>
</a>
