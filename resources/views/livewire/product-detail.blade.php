<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ qty: @entangle('qty'), maxStock: {{ $product->stock }} }">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-surface-500 mb-6">
        <a href="{{ url('/') }}" wire:navigate class="hover:text-primary-500 transition-colors">Beranda</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ url('/products') }}" wire:navigate class="hover:text-primary-500 transition-colors">Produk</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-surface-800 font-medium">{{ $product->name }}</span>
    </nav>

    {{-- Product Detail --}}
    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
        {{-- Image Gallery --}}
        <div>
            <div class="bg-white rounded-2xl overflow-hidden shadow-card aspect-square flex items-center justify-center">
                @if($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                        <svg class="w-24 h-24 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        {{-- Product Info --}}
        <div>
            @if($product->category)
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-primary-50 text-primary-600 text-xs font-semibold mb-3">{{ $product->category->name }}</span>
            @endif

            <h1 class="font-heading text-2xl sm:text-3xl font-bold text-surface-900">{{ $product->name }}</h1>

            {{-- Sold + Rating --}}
            <div class="flex items-center gap-4 mt-3">
                @if($product->rating_count > 0)
                    <div class="flex items-center gap-1">
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= round($product->avg_rating) ? 'text-yellow-400' : 'text-surface-300' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            @endfor
                        </div>
                        <span class="text-sm font-semibold text-surface-700">{{ number_format($product->avg_rating, 1) }}</span>
                        <span class="text-sm text-surface-400">({{ $product->rating_count }})</span>
                    </div>
                    <span class="text-surface-300">|</span>
                @endif
                <span class="text-sm text-surface-500">{{ $product->sold_quantity }} terjual</span>
            </div>

            {{-- Price --}}
            <div class="mt-5 p-4 bg-surface-50 rounded-xl">
                <p class="text-3xl font-heading font-extrabold text-primary-500">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                <p class="text-sm text-surface-500 mt-1">Stok Tersedia: {{ $product->stock }} pcs</p>
            </div>

            {{-- Description --}}
            <div class="mt-6">
                <h3 class="font-semibold text-surface-800 mb-2">Deskripsi Produk</h3>
                <div class="text-sm text-surface-600 leading-relaxed space-y-2">
                    <p>{{ $product->description }}</p>
                </div>
            </div>

            {{-- Quantity & Add to Cart --}}
            <div class="mt-6 flex flex-wrap items-center gap-4">
                <div class="flex items-center border border-surface-300 rounded-xl overflow-hidden">
                    <button @click="qty = Math.max(1, qty - 1)" class="px-3 py-2.5 hover:bg-surface-50 transition-colors text-surface-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <input type="number" x-model.number="qty" min="1" max="{{ $product->stock }}" class="w-14 text-center border-x border-surface-300 py-2 text-sm font-medium [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    <button @click="qty = Math.min({{ $product->stock }}, qty + 1)" class="px-3 py-2.5 hover:bg-surface-50 transition-colors text-surface-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>

                <button
                    wire:click="addToCart"
                    wire:loading.attr="disabled"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-primary-500 text-primary-500 font-semibold rounded-xl hover:bg-primary-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="addToCart" class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        Tambah Keranjang
                    </span>
                    <span wire:loading wire:target="addToCart" class="flex items-center gap-2">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>

                <button
                    wire:click="buyNow"
                    wire:loading.attr="disabled"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="buyNow">Beli Sekarang</span>
                    <span wire:loading wire:target="buyNow" class="flex items-center gap-2">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </div>

            {{-- Seller Card --}}
            @if($product->store)
                <div class="mt-8 p-4 bg-white border border-surface-200 rounded-xl flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center shrink-0">
                        <span class="font-heading font-bold text-primary-600">{{ strtoupper(substr($product->store->name, 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="{{ url('/stores/' . $product->store->slug) }}" wire:navigate class="font-semibold text-surface-800 hover:text-primary-500 transition-colors">{{ $product->store->name }}</a>
                        <div class="flex items-center gap-2 mt-0.5">
                            <span class="text-xs text-surface-500">{{ $product->store->products()->count() }} produk</span>
                        </div>
                    </div>
                    <a href="{{ url('/stores/' . $product->store->slug) }}" wire:navigate class="px-4 py-2 text-sm font-medium text-primary-500 border border-primary-500 rounded-lg hover:bg-primary-50 transition-colors shrink-0">
                        Kunjungi Toko
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Ratings & Reviews --}}
    <section class="mt-12">
        <h2 class="font-heading text-xl font-bold text-surface-900 mb-6">Ulasan & Rating</h2>

        @php $reviews = $product->ratings()->with('user')->latest()->limit(10)->get(); @endphp

        @if($reviews->isEmpty())
            <div class="bg-white rounded-2xl shadow-card p-8 text-center">
                <svg class="w-12 h-12 text-surface-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                <p class="text-surface-500 text-sm">Belum ada ulasan untuk produk ini.</p>
                <p class="text-surface-400 text-xs mt-1">Jadilah yang pertama memberikan rating!</p>
            </div>
        @else
            {{-- Summary --}}
            <div class="bg-white rounded-2xl shadow-card p-5 mb-4 flex items-center gap-4">
                <div class="text-center">
                    <p class="text-4xl font-heading font-extrabold text-primary-500">{{ number_format($product->avg_rating, 1) }}</p>
                    <div class="flex items-center justify-center mt-1">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 {{ $i <= round($product->avg_rating) ? 'text-yellow-400' : 'text-surface-300' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <p class="text-xs text-surface-400 mt-1">{{ $product->rating_count }} ulasan</p>
                </div>
                <div class="flex-1 space-y-1">
                    @for($star = 5; $star >= 1; $star--)
                        @php $count = $reviews->where('rating', $star)->count(); $pct = $product->rating_count > 0 ? ($count / $product->rating_count * 100) : 0; @endphp
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-8 text-right text-surface-500">{{ $star }} &#9733;</span>
                            <div class="flex-1 h-2 bg-surface-100 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-400 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="w-6 text-surface-400">{{ $count }}</span>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Review List --}}
            <div class="space-y-3">
                @foreach($reviews as $review)
                    <div class="bg-white rounded-xl shadow-card p-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-xs font-bold text-primary-600">{{ strtoupper(substr($review->user->name, 0, 1)) }}</div>
                            <div>
                                <p class="text-sm font-semibold text-surface-800">{{ $review->user->name }}</p>
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3 h-3 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-surface-300' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    @endfor
                                    <span class="text-xs text-surface-400 ml-1">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        @if($review->comment)
                            <p class="text-sm text-surface-600 leading-relaxed ml-11">{{ $review->comment }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Related Products --}}
    <section class="mt-12">
        <h2 class="font-heading text-xl font-bold text-surface-900 mb-6">Produk Terkait</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            @foreach($relatedProducts as $rel)
                <div class="group bg-white rounded-2xl overflow-hidden shadow-card card-hover flex flex-col justify-between h-full">
                    <a href="{{ url('/products/' . $rel->slug) }}" wire:navigate class="block relative overflow-hidden aspect-square shrink-0">
                        @if($rel->image)
                            <img src="{{ asset('storage/' . $rel->image) }}" alt="{{ $rel->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif
                    </a>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <a href="{{ url('/products/' . $rel->slug) }}" wire:navigate class="block">
                                <h3 class="font-semibold text-surface-800 text-xs line-clamp-2" x-text="'{{ addslashes($rel->name) }}'"></h3>
                            </a>
                            <p class="text-primary-500 font-bold text-sm mt-1">Rp {{ number_format($rel->price, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
