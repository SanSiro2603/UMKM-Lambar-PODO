@extends('layouts.app')

@section('title', 'Kopi Robusta Lampung Barat Premium')
@section('meta_description', 'Kopi Robusta Lampung Barat Premium — kopi pilihan langsung dari kebun Lampung Barat. Rasa bold dan aroma kuat.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-sm text-surface-500 mb-6">
        <a href="{{ url('/') }}" class="hover:text-primary-500 transition-colors">Beranda</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ url('/products') }}" class="hover:text-primary-500 transition-colors">Produk</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-surface-800 font-medium">Kopi Robusta Lampung Barat Premium</span>
    </nav>

    {{-- Product Detail --}}
    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12" x-data="{ mainImage: 0, qty: 1 }">
        {{-- Image Gallery --}}
        <div>
            <div class="bg-white rounded-2xl overflow-hidden shadow-card aspect-square flex items-center justify-center">
                <div class="w-full h-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                    <svg class="w-24 h-24 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            {{-- Thumbnails --}}
            <div class="flex gap-3 mt-4">
                @for($i = 0; $i < 4; $i++)
                    <button @click="mainImage = {{ $i }}"
                            :class="mainImage === {{ $i }} ? 'ring-2 ring-primary-500' : 'ring-1 ring-surface-200'"
                            class="w-20 h-20 rounded-xl overflow-hidden bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center transition-all">
                        <svg class="w-8 h-8 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </button>
                @endfor
            </div>
        </div>

        {{-- Product Info --}}
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-primary-50 text-primary-600 text-xs font-semibold mb-3">Minuman</span>

            <h1 class="font-heading text-2xl sm:text-3xl font-bold text-surface-900">Kopi Robusta Lampung Barat Premium</h1>

            {{-- Rating & Sold --}}
            <div class="flex items-center gap-4 mt-3">
                <div class="flex items-center gap-1">
                    @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5 {{ $i < 4 ? 'text-amber-400' : 'text-surface-300' }} fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                    <span class="text-sm font-semibold text-surface-700 ml-1">4.8</span>
                </div>
                <span class="text-surface-300">|</span>
                <span class="text-sm text-surface-500">0 terjual</span>
                <span class="text-surface-300">|</span>
                <span class="text-sm text-surface-500">42 ulasan</span>
            </div>

            {{-- Price --}}
            <div class="mt-5 p-4 bg-surface-50 rounded-xl">
                <p class="text-3xl font-heading font-extrabold text-primary-500">Rp 75.000</p>
                <p class="text-sm text-surface-500 mt-1">Per 250 gram</p>
            </div>

            {{-- Description --}}
            <div class="mt-6">
                <h3 class="font-semibold text-surface-800 mb-2">Deskripsi Produk</h3>
                <div class="text-sm text-surface-600 leading-relaxed space-y-2">
                    <p>Kopi Robusta Lampung Barat Premium adalah kopi pilihan yang dipetik langsung dari kebun kopi di wilayah Lampung Barat. Diproses secara alami (natural process) untuk menghasilkan rasa yang bold, aroma kuat, dan body yang tebal.</p>
                    <p>Cocok untuk pecinta kopi yang menyukai karakter rasa kuat dengan sedikit rasa coklat dan kacang. Bisa diseduh dengan berbagai metode: tubruk, V60, French Press, atau espresso.</p>
                </div>
            </div>

            {{-- Stock --}}
            <div class="mt-5 flex items-center gap-2">
                <span class="text-sm text-surface-500">Stok:</span>
                <span class="text-sm font-semibold text-green-600">Tersedia (48 pcs)</span>
            </div>

            {{-- Quantity & Add to Cart --}}
            <div class="mt-6 flex flex-wrap items-center gap-4">
                <div class="flex items-center border border-surface-300 rounded-xl overflow-hidden">
                    <button @click="qty = Math.max(1, qty - 1)" class="px-3 py-2.5 hover:bg-surface-50 transition-colors text-surface-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <input type="number" x-model.number="qty" min="1" max="48" class="w-14 text-center border-x border-surface-300 py-2 text-sm font-medium [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    <button @click="qty = Math.min(48, qty + 1)" class="px-3 py-2.5 hover:bg-surface-50 transition-colors text-surface-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                </div>

                <button @click="$store.cart.add({ id: 1, name: 'Kopi Robusta Lampung Barat Premium', price: 75000, qty: qty, seller: 'Toko Kopi Pak Adi', image: null })"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    Tambah Keranjang
                </button>

                <button class="px-4 py-3 border-2 border-primary-500 text-primary-500 font-semibold rounded-xl hover:bg-primary-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </button>
            </div>

            {{-- Seller Card --}}
            <div class="mt-8 p-4 bg-white border border-surface-200 rounded-xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center shrink-0">
                    <span class="font-heading font-bold text-primary-600">T</span>
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ url('/stores/toko-kopi-pak-adi') }}" class="font-semibold text-surface-800 hover:text-primary-500 transition-colors">Toko Kopi Pak Adi</a>
                    <div class="flex items-center gap-2 mt-0.5">
                        <div class="flex items-center gap-0.5">
                            <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span class="text-xs font-medium text-surface-600">4.8</span>
                        </div>
                        <span class="text-surface-300 text-xs">|</span>
                        <span class="text-xs text-surface-500">12 produk</span>
                    </div>
                </div>
                <a href="{{ url('/stores/toko-kopi-pak-adi') }}" class="px-4 py-2 text-sm font-medium text-primary-500 border border-primary-500 rounded-lg hover:bg-primary-50 transition-colors shrink-0">
                    Kunjungi Toko
                </a>
            </div>
        </div>
    </div>

    {{-- Reviews --}}
    <section class="mt-12">
        <h2 class="font-heading text-xl font-bold text-surface-900 mb-6">Ulasan Pembeli (42)</h2>
        <div class="space-y-4">
            @php
                $reviews = [
                    ['name' => 'Ahmad S.', 'rating' => 5, 'date' => '2 hari lalu', 'text' => 'Kopi terbaik dari Lampung Barat! Aroma sangat harum dan rasa bold-nya pas banget. Pengiriman juga cepat dan packing aman.'],
                    ['name' => 'Siti R.', 'rating' => 5, 'date' => '5 hari lalu', 'text' => 'Sudah repeat order ke-3. Kualitas konsisten, roasting merata. Recommended!'],
                    ['name' => 'Budi P.', 'rating' => 4, 'date' => '1 minggu lalu', 'text' => 'Kopinya enak, body tebal. Cuma kemasan agak kecil untuk harganya. Tapi overall puas.'],
                ];
            @endphp
            @foreach($reviews as $review)
                <div class="bg-white p-5 rounded-xl border border-surface-100">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="font-semibold text-sm text-primary-600">{{ strtoupper(substr($review['name'], 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-surface-800 text-sm">{{ $review['name'] }}</p>
                                <div class="flex items-center gap-0.5 mt-0.5">
                                    @for($i = 0; $i < $review['rating']; $i++)
                                        <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-surface-400">{{ $review['date'] }}</span>
                    </div>
                    <p class="text-sm text-surface-600 mt-3 leading-relaxed">{{ $review['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Related Products --}}
    <section class="mt-12">
        <h2 class="font-heading text-xl font-bold text-surface-900 mb-6">Produk Terkait</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <x-product-card name="Kopi Liberika Lampung Barat" price="95000" category="Minuman" seller="Toko Kopi Pak Adi" rating="4.8" sold="0" slug="kopi-liberika" sellerSlug="toko-kopi-pak-adi" />
            <x-product-card name="Gula Aren Organik 1kg" price="45000" category="Pertanian" seller="Tani Makmur" rating="4.7" sold="0" slug="gula-aren" sellerSlug="tani-makmur" />
            <x-product-card name="Teh Herbal Daun Kelor" price="28000" category="Minuman" seller="Tani Makmur" rating="4.4" sold="0" slug="teh-herbal" sellerSlug="tani-makmur" />
            <x-product-card name="Madu Hutan Asli Lampung Barat 500ml" price="120000" category="Pertanian" seller="Madu Rimba" rating="4.9" sold="0" slug="madu-hutan" sellerSlug="madu-rimba" />
        </div>
    </section>
</div>
@endsection
