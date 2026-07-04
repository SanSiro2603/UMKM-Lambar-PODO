<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">

    {{-- Store Header --}}
    <section class="bg-white rounded-2xl overflow-hidden shadow-card mb-8">
        {{-- Banner --}}
        <div class="relative h-40 sm:h-56 overflow-hidden bg-surface-100">
            @if($store->banner)
                <img src="{{ asset('storage/' . $store->banner) }}" alt="{{ $store->name }} Banner" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-primary-900/35 via-primary-900/5 to-transparent"></div>
            @else
                <div class="absolute inset-0 hero-gradient"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.18),transparent_28%),radial-gradient(circle_at_82%_18%,rgba(212,168,67,0.24),transparent_26%)]"></div>
            @endif
        </div>

        {{-- Store Info --}}
        <div class="relative px-5 sm:px-7 lg:px-8 pb-6 sm:pb-7">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between -mt-12 sm:-mt-14">
                <div class="flex flex-col sm:flex-row sm:items-end gap-4 min-w-0">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white shadow-card flex items-center justify-center border-4 border-white shrink-0 overflow-hidden">
                        @if($store->logo)
                            <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }} Logo" class="w-full h-full object-cover">
                        @else
                            <span class="font-heading font-bold text-4xl text-primary-500">{{ strtoupper(substr($store->name, 0, 1)) }}</span>
                        @endif
                    </div>

                    <div class="min-w-0 sm:pb-2">
                        <h1 class="font-heading text-2xl sm:text-3xl font-bold text-surface-900">{{ $store->name }}</h1>
                        <p class="text-surface-500 text-sm sm:text-base leading-relaxed mt-2 max-w-3xl">
                            {{ $store->description ?? 'Tidak ada deskripsi toko.' }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    x-data
                    @click="navigator.clipboard?.writeText(window.location.href); window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Link toko berhasil disalin.', type: 'success' } }))"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-primary-500 border border-primary-500 rounded-xl hover:bg-primary-50 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    Bagikan
                </button>
            </div>

            {{-- Stats --}}
            <div class="mt-6 pt-5 border-t border-surface-100 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="flex items-start gap-3 text-sm text-surface-500 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <span class="pt-1.5"><span class="font-bold text-surface-800">{{ $products->count() }}</span> Produk</span>
                </div>
                <div class="flex items-start gap-3 text-sm text-surface-500 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-yellow-50 text-yellow-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span class="pt-1.5">
                        @if($store->rating_count > 0)
                            <span class="font-bold text-surface-800">{{ number_format($store->avg_rating, 1) }}</span>
                            <span class="text-surface-400"> ({{ $store->rating_count }} ulasan)</span>
                        @else
                            Belum ada rating
                        @endif
                    </span>
                </div>
                <div class="flex items-start gap-3 text-sm text-surface-500 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <span class="pt-1.5 leading-relaxed line-clamp-2">{{ $store->address ?? 'Kabupaten Lampung Barat' }}</span>
                </div>
                <div class="flex items-start gap-3 text-sm text-surface-500 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="pt-1.5">Bergabung sejak {{ $store->created_at->format('M Y') }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Products --}}
    <section>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-5">
            <div>
                <h2 class="font-heading text-xl sm:text-2xl font-bold text-surface-900">Semua Produk</h2>
                <p class="text-sm text-surface-500 mt-1">{{ $products->count() }} produk tersedia dari {{ $store->name }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @forelse($products as $product)
                <div class="group bg-white rounded-2xl overflow-hidden shadow-card card-hover flex flex-col h-full">
                    {{-- Image --}}
                    <a href="{{ url('/products/' . $product->slug) }}" wire:navigate class="block relative overflow-hidden aspect-square shrink-0">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif
                        @if($product->category)
                            <span class="absolute top-3 left-3 max-w-[calc(100%-1.5rem)] px-2.5 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-semibold text-primary-600 truncate">{{ $product->category->name }}</span>
                        @endif
                    </a>

                    {{-- Info --}}
                    <div class="p-4 flex-1 flex flex-col">
                        <a href="{{ url('/products/' . $product->slug) }}" wire:navigate class="block">
                            <h3 class="font-semibold text-surface-800 text-sm line-clamp-2 group-hover:text-primary-500 transition-colors">{{ $product->name }}</h3>
                        </a>
                        <p class="text-primary-500 font-bold text-base sm:text-lg mt-2">Rp {{ number_format($product->price, 0, ',', '.') }}</p>

                        <div class="mt-auto pt-3 text-xs text-surface-500">
                            <span>{{ $product->sold_quantity }} terjual</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl shadow-card px-6 py-14 text-center">
                    <div class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-bold text-surface-900">Belum Ada Produk</h3>
                    <p class="text-sm text-surface-500 mt-1">Toko ini belum menambahkan produk.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
