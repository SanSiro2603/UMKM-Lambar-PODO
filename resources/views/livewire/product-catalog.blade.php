<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-surface-500 mb-6">
        <a href="{{ url('/') }}" wire:navigate class="hover:text-primary-500 transition-colors">Beranda</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-surface-800 font-medium">Semua Produk</span>
    </nav>

    <div class="flex gap-8">
        {{-- SIDEBAR FILTER (Desktop) --}}
        <aside class="hidden lg:block w-64 shrink-0">
            <div class="sticky top-24 space-y-6">
                <div class="bg-white rounded-2xl p-5 shadow-card">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-heading font-bold text-surface-900">Filter</h3>
                        <button wire:click="resetFilters" class="text-xs font-semibold text-primary-500 hover:text-primary-600">
                            Reset
                        </button>
                    </div>

                    {{-- Kategori --}}
                    <div class="mb-5">
                        <h4 class="text-sm font-semibold text-surface-700 mb-3">Kategori</h4>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="category_desktop" value="Semua" wire:model.live="categoryFilter"
                                       class="w-4 h-4 border-surface-300 text-primary-500 focus:ring-primary-200">
                                <span class="text-sm text-surface-600 group-hover:text-surface-800" :class="categoryFilter === 'Semua' && 'text-primary-600 font-semibold'">Semua</span>
                            </label>
                            @foreach($categoriesList as $cat)
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="category_desktop" value="{{ $cat->name }}" wire:model.live="categoryFilter"
                                           class="w-4 h-4 border-surface-300 text-primary-500 focus:ring-primary-200">
                                    <span class="text-sm text-surface-600 group-hover:text-surface-800" :class="categoryFilter === '{{ $cat->name }}' && 'text-primary-600 font-semibold'">{{ $cat->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Harga --}}
                    <div class="mb-5">
                        <h4 class="text-sm font-semibold text-surface-700 mb-3">Rentang Harga</h4>
                        <div class="space-y-2">
                            <input type="number" placeholder="Harga Minimum" wire:model.live.debounce.300ms="minPrice" class="w-full px-3 py-2 text-sm border border-surface-300 rounded-lg focus:border-primary-400 focus:ring-1 focus:ring-primary-200">
                            <input type="number" placeholder="Harga Maksimum" wire:model.live.debounce.300ms="maxPrice" class="w-full px-3 py-2 text-sm border border-surface-300 rounded-lg focus:border-primary-400 focus:ring-1 focus:ring-primary-200">
                        </div>
                    </div>

                </div>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <div class="flex-1 min-w-0">
            {{-- Top Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <p class="text-sm text-surface-500">
                        Menampilkan <span class="font-semibold text-primary-500">{{ $products->total() }}</span> produk 
                        @if($searchQuery)
                            untuk pencarian "<span class="font-semibold">{{ $searchQuery }}</span>"
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3 justify-between sm:justify-end">
                    {{-- Mobile Filter Toggle --}}
                    <button wire:click="$set('filterOpen', true)" class="lg:hidden flex items-center gap-2 px-4 py-2 bg-white border border-surface-300 rounded-xl text-sm font-medium text-surface-700 hover:border-primary-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter
                    </button>

                    {{-- Sort --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-surface-500 font-semibold uppercase tracking-wider">Urutkan:</span>
                        <select wire:model.live="sort" class="px-3 py-2 bg-white border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-200">
                            <option value="terbaru">Terbaru</option>
                            <option value="termurah">Termurah</option>
                            <option value="termahal">Termahal</option>
                            <option value="terlaris">Terlaris</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                @forelse($products as $product)
                    <div class="group bg-white rounded-2xl overflow-hidden shadow-card card-hover flex flex-col justify-between h-full">
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
                                <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-semibold text-primary-600">{{ $product->category->name }}</span>
                            @endif
                        </a>

                        {{-- Info --}}
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <a href="{{ url('/products/' . $product->slug) }}" wire:navigate class="block">
                                    <h3 class="font-semibold text-surface-800 text-sm line-clamp-2 group-hover:text-primary-500 transition-colors">{{ $product->name }}</h3>
                                </a>
                                <p class="text-primary-500 font-bold text-base mt-2">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                            </div>

                            <div class="mt-3">
                                {{-- Sold --}}
                                <div class="flex items-center gap-2 text-[11px] text-surface-500">
                                    <span>{{ $product->sold_quantity }} terjual</span>
                                </div>

                                {{-- Seller --}}
                                @if($product->store)
                                    <div class="flex items-center gap-1.5 mt-3 pt-3 border-t border-surface-100">
                                        <div class="w-5 h-5 rounded-full bg-primary-50 flex items-center justify-center shrink-0">
                                            <span class="text-[9px] font-bold text-primary-600">{{ strtoupper(substr($product->store->name, 0, 1)) }}</span>
                                        </div>
                                        <a href="{{ url('/stores/' . $product->store->slug) }}" wire:navigate class="text-xs text-surface-500 hover:text-primary-500 transition-colors truncate">{{ $product->store->name }}</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16 bg-white rounded-3xl shadow-card mt-6">
                        <div class="w-16 h-16 bg-surface-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h3 class="font-heading text-lg font-bold text-surface-800 mb-1">Produk Tidak Ditemukan</h3>
                        <p class="text-surface-500 text-sm max-w-sm mx-auto">Kami tidak dapat menemukan produk yang sesuai dengan filter atau kata kunci Anda.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            <div class="mt-10">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    {{-- Mobile Filter Drawer --}}
    <div x-show="$wire.filterOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
        <div class="absolute inset-0 bg-black/50" @click="$wire.set('filterOpen', false)"></div>
        <div x-show="$wire.filterOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="absolute right-0 inset-y-0 w-80 bg-white shadow-xl p-6 overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-heading text-lg">Filter</h3>
                <button @click="$wire.set('filterOpen', false)" class="p-1 rounded-lg hover:bg-surface-100">
                    <svg class="w-5 h-5 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Kategori --}}
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-surface-700 mb-3">Kategori</h4>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="category_mobile" value="Semua" wire:model.live="categoryFilter"
                               class="w-4 h-4 border-surface-300 text-primary-500 focus:ring-primary-200">
                        <span class="text-sm text-surface-600" :class="categoryFilter === 'Semua' && 'text-primary-600 font-semibold'">Semua</span>
                    </label>
                    @foreach($categoriesList as $cat)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="category_mobile" value="{{ $cat->name }}" wire:model.live="categoryFilter"
                                   class="w-4 h-4 border-surface-300 text-primary-500 focus:ring-primary-200">
                            <span class="text-sm text-surface-600" :class="categoryFilter === '{{ $cat->name }}' && 'text-primary-600 font-semibold'">{{ $cat->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Harga --}}
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-surface-700 mb-3">Rentang Harga</h4>
                <div class="space-y-2">
                    <input type="number" placeholder="Harga Minimum" wire:model.live.debounce.300ms="minPrice" class="w-full px-3 py-2 text-sm border border-surface-300 rounded-lg">
                    <input type="number" placeholder="Harga Maksimum" wire:model.live.debounce.300ms="maxPrice" class="w-full px-3 py-2 text-sm border border-surface-300 rounded-lg">
                </div>
            </div>


            <button @click="$wire.set('filterOpen', false)" class="w-full py-2.5 bg-primary-500 text-white text-sm font-semibold rounded-xl hover:bg-primary-600 transition-colors">
                Terapkan Filter
            </button>
        </div>
    </div>
</div>
