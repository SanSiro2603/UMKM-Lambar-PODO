@extends('layouts.app')

@section('title', 'Daftar Toko UMKM')
@section('meta_description', 'Jelajahi toko-toko UMKM terpercaya di Lampung Barat. Beli produk lokal langsung dari produsennya.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ 
    search: '', 
    sortBy: 'rating',
    stores: [
        { name: 'Toko Kopi Pak Adi', slug: 'toko-kopi-pak-adi', description: 'Kopi pilihan langsung dari kebun di Lampung Barat.', rating: 4.8, products: 12, category: 'minuman' },
        { name: 'Snack Nusantara', slug: 'snack-nusantara', description: 'Aneka keripik dan camilan khas Lampung Barat renyah berkualitas.', rating: 4.7, products: 24, category: 'makanan' },
        { name: 'Rotan Jaya', slug: 'rotan-jaya', description: 'Kerajinan tangan dari anyaman rotan berkualitas ekspor.', rating: 4.9, products: 18, category: 'kerajinan' },
        { name: 'Batik Lampung Barat', slug: 'batik-lampung-barat', description: 'Batik tradisional khas Lampung Barat dengan pewarnaan alami.', rating: 4.6, products: 15, category: 'pakaian' },
        { name: 'Madu Rimba', slug: 'madu-rimba', description: 'Madu hutan murni dari kawasan Lampung Barat.', rating: 4.9, products: 8, category: 'pertanian' },
        { name: 'Tani Makmur', slug: 'tani-makmur', description: 'Hasil tani dan gula aren organik berkualitas tinggi.', rating: 4.7, products: 10, category: 'pertanian' }
    ],
    get filteredStores() {
        let filtered = this.stores.filter(s => s.name.toLowerCase().includes(this.search.toLowerCase()) || s.description.toLowerCase().includes(this.search.toLowerCase()));
        
        if (this.sortBy === 'rating') {
            return filtered.sort((a, b) => b.rating - a.rating);
        } else if (this.sortBy === 'products') {
            return filtered.sort((a, b) => b.products - a.products);
        } else if (this.sortBy === 'name') {
            return filtered.sort((a, b) => a.name.localeCompare(b.name));
        }
        return filtered;
    }
}">
    {{-- Header --}}
    <div class="mb-8 text-center sm:text-left">
        <h1 class="font-heading text-3xl font-bold text-surface-900">Jelajahi Toko UMKM</h1>
        <p class="text-surface-500 mt-2">Dukung langsung perekonomian lokal dengan membeli dari produsen terpercaya di Lampung Barat.</p>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="bg-white rounded-2xl shadow-card p-5 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between">
        {{-- Search --}}
        <div class="relative w-full md:max-w-md">
            <input type="text" placeholder="Cari nama toko atau deskripsi..." x-model="search"
                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-surface-300 bg-surface-50/50 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        {{-- Sort --}}
        <div class="flex items-center gap-3 w-full md:w-auto justify-end">
            <span class="text-xs text-surface-500 font-semibold uppercase tracking-wider whitespace-nowrap">Urutkan:</span>
            <select x-model="sortBy"
                    class="rounded-xl border border-surface-300 px-4 py-2.5 bg-surface-50/50 text-sm font-medium text-surface-700 focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                <option value="rating">Rating Tertinggi</option>
                <option value="products">Produk Terbanyak</option>
                <option value="name">Nama (A-Z)</option>
            </select>
        </div>
    </div>

    {{-- Grid Toko --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="store in filteredStores" :key="store.slug">
            <a :href="'{{ url('/stores') }}/' + store.slug" class="group block bg-white rounded-2xl overflow-hidden shadow-card card-hover">
                {{-- Banner --}}
                <div class="h-24 hero-gradient relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-primary-500/50 to-transparent"></div>
                </div>

                {{-- Info --}}
                <div class="px-5 pb-5 -mt-8 relative">
                    {{-- Avatar --}}
                    <div class="w-16 h-16 rounded-2xl bg-white shadow-card flex items-center justify-center border-4 border-white mb-3 shrink-0">
                        <span class="font-heading font-bold text-2xl text-primary-500" x-text="store.name.substring(0, 1)"></span>
                    </div>

                    <h3 class="font-heading font-bold text-lg text-surface-800 group-hover:text-primary-500 transition-colors" x-text="store.name"></h3>
                    <p class="text-sm text-surface-500 mt-2 line-clamp-2" x-text="store.description"></p>

                    <div class="flex items-center gap-4 mt-4 pt-4 border-t border-surface-50 text-xs text-surface-500">
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="font-bold text-surface-700" x-text="store.rating.toFixed(1)"></span>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span><span class="font-bold text-surface-700" x-text="store.products"></span> produk</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            <span>Lampung Barat</span>
                        </div>
                    </div>
                </div>
            </a>
        </template>
    </div>

    {{-- Empty State --}}
    <div x-show="filteredStores.length === 0" class="text-center py-16 bg-white rounded-3xl shadow-card mt-6">
        <div class="w-16 h-16 bg-surface-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72"/></svg>
        </div>
        <h3 class="font-heading text-lg font-bold text-surface-800 mb-1">Toko Tidak Ditemukan</h3>
        <p class="text-surface-500 text-sm max-w-sm mx-auto">Tidak ada toko UMKM yang cocok dengan pencarian Anda. Coba kata kunci lain.</p>
    </div>
</div>
@endsection
