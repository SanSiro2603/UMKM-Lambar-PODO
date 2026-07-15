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
    'avgRating' => 0,
    'ratingCount' => 0,
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

        {{-- Rating + Sold --}}
        <div class="flex items-center justify-between mt-2">
            @if($ratingCount > 0)
                <div class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-yellow-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="text-xs font-semibold text-surface-700">{{ number_format($avgRating, 1) }}</span>
                    <span class="text-xs text-surface-400">({{ $ratingCount }})</span>
                </div>
            @else
                <span class="text-xs text-surface-400 italic">Belum ada ulasan</span>
            @endif
            <span class="text-xs text-surface-500">{{ $sold }} terjual</span>
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
