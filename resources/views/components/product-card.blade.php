@props([
    'id' => null,
    'name' => 'Produk',
    'price' => 0,
    'image' => null,
    'category' => '',
    'seller' => '',
    'sellerSlug' => '',
    'sold' => 0,
    'slug' => '#',
])

<div class="group bg-white rounded-2xl overflow-hidden shadow-card card-hover">
    {{-- Image --}}
    <a href="{{ url('/products/' . ($slug ?? 'detail')) }}" wire:navigate class="block relative overflow-hidden aspect-square">
        @if($image)
            <img src="{{ $image }}" alt="{{ $name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
            <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                <svg class="w-12 h-12 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        @endif
        @if($category)
            <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-medium text-surface-700">{{ $category }}</span>
        @endif
    </a>

    {{-- Info --}}
    <div class="p-4">
        <a href="{{ url('/products/' . ($slug ?? 'detail')) }}" wire:navigate class="block">
            <h3 class="font-semibold text-surface-800 text-sm line-clamp-2 group-hover:text-primary-500 transition-colors">{{ $name }}</h3>
        </a>

        <p class="text-primary-500 font-bold text-lg mt-1.5">{{ 'Rp ' . number_format($price, 0, ',', '.') }}</p>

        {{-- Sold --}}
        <div class="flex items-center gap-2 mt-2 text-xs text-surface-500">
            <span>{{ $sold }} terjual</span>
        </div>

        {{-- Seller --}}
        @if($seller)
            <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-surface-100">
                <div class="w-5 h-5 rounded-full bg-primary-100 flex items-center justify-center">
                    <span class="text-[10px] font-bold text-primary-600">{{ strtoupper(substr($seller, 0, 1)) }}</span>
                </div>
                <a href="{{ url('/stores/' . ($sellerSlug ?? 'toko')) }}" wire:navigate class="text-xs text-surface-500 hover:text-primary-500 transition-colors truncate">{{ $seller }}</a>
            </div>
        @endif
    </div>
</div>
