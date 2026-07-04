<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="mb-8 text-center sm:text-left">
        <h1 class="font-heading text-3xl font-bold text-surface-900">Jelajahi Toko UMKM</h1>
        <p class="text-surface-500 mt-2">Dukung langsung perekonomian lokal dengan membeli dari produsen terpercaya di Lampung Barat.</p>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white rounded-2xl shadow-card p-5 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
        {{-- Search --}}
        <div class="relative w-full md:max-w-md">
            <input type="text" placeholder="Cari nama toko atau deskripsi..." wire:model.live.debounce.300ms="search"
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-surface-300 bg-surface-50/50 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        {{-- Sort --}}
        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
            <span class="text-xs text-surface-500 font-semibold uppercase tracking-wider whitespace-nowrap">Urutkan:</span>
            <select wire:model.live="sortBy"
                    class="rounded-xl border border-surface-300 px-4 py-2.5 bg-surface-50/50 text-sm font-medium text-surface-700 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <option value="name">Nama (A-Z)</option>
                <option value="products">Produk Terbanyak</option>
            </select>
        </div>
    </div>

    {{-- Grid Toko --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($stores as $store)
            <a href="{{ url('/stores/' . $store->slug) }}" wire:navigate class="group block bg-white rounded-2xl overflow-hidden shadow-card card-hover">
                {{-- Banner --}}
                <div class="h-24 relative overflow-hidden bg-surface-100">
                    @if($store->banner)
                        <img src="{{ asset('storage/' . $store->banner) }}" alt="{{ $store->name }} Banner" class="w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 hero-gradient"></div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-500/50 to-transparent"></div>
                </div>

                {{-- Info --}}
                <div class="px-5 pb-5 -mt-8 relative">
                    {{-- Avatar --}}
                    <div class="w-16 h-16 rounded-2xl bg-white shadow-card flex items-center justify-center border-4 border-white mb-3 shrink-0 overflow-hidden">
                        @if($store->logo)
                            <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }} Logo" class="w-full h-full object-cover">
                        @else
                            <span class="font-heading font-bold text-2xl text-primary-500">{{ strtoupper(substr($store->name, 0, 1)) }}</span>
                        @endif
                    </div>

                    <h3 class="font-heading font-bold text-lg text-surface-800 group-hover:text-primary-500 transition-colors">{{ $store->name }}</h3>
                    <p class="text-sm text-surface-500 mt-2 line-clamp-2">{{ $store->description }}</p>

                    <div class="flex items-center gap-4 mt-4 pt-4 border-t border-surface-50 text-xs text-surface-500">
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span><span class="font-bold text-surface-700">{{ $store->products()->count() }}</span> produk</span>
                        </div>
                        @if($store->rating_count > 0)
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span><span class="font-bold text-surface-700">{{ number_format($store->avg_rating, 1) }}</span> ({{ $store->rating_count }})</span>
                        </div>
                        @endif
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>Lampung Barat</span>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-16 bg-white rounded-3xl shadow-card mt-6">
                <div class="w-16 h-16 bg-surface-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35"/></svg>
                </div>
                <h3 class="font-heading text-lg font-bold text-surface-800 mb-1">Toko Tidak Ditemukan</h3>
                <p class="text-surface-500 text-sm max-w-sm mx-auto">Tidak ada toko UMKM yang cocok dengan pencarian Anda. Coba kata kunci lain.</p>
            </div>
        @endforelse
    </div>
</div>
