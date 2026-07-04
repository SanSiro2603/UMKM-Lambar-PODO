@extends('layouts.app')

@section('title', 'Panduan Belanja & Jualan')
@section('meta_description', 'Panduan cara berbelanja dan cara menjadi seller di UMKM Lampung Barat. Mudah, aman, dan mendukung ekonomi lokal.')

@section('content')

{{-- Hero --}}
<section class="bg-gradient-to-br from-primary-900 via-primary-800 to-primary-700 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-block px-3 py-1 bg-white/10 text-white/90 text-xs font-semibold rounded-full uppercase tracking-wider mb-4">Panduan Penggunaan</span>
        <h1 class="font-heading text-4xl sm:text-5xl font-extrabold text-white leading-tight">
            Cara Belanja & <span class="text-accent-400">Cara Jualan</span>
        </h1>
        <p class="mt-4 text-primary-200 text-lg max-w-xl mx-auto">Ikuti langkah-langkah berikut untuk mulai berbelanja atau membuka toko di UMKM Lampung Barat.</p>

        {{-- Tab anchor buttons --}}
        <div class="mt-8 flex justify-center gap-4">
            <a href="#cara-belanja" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-primary-800 font-bold rounded-xl hover:bg-primary-50 transition-all shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Cara Berbelanja
            </a>
            <a href="#cara-jualan" class="inline-flex items-center gap-2 px-6 py-3 bg-accent-400 text-primary-900 font-bold rounded-xl hover:bg-accent-300 transition-all shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72"/></svg>
                Cara Jadi Seller
            </a>
        </div>
    </div>
</section>

{{-- ============================================ --}}
{{-- CARA BERBELANJA --}}
{{-- ============================================ --}}
<section id="cara-belanja" class="py-20 bg-white scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="inline-block px-3 py-1 bg-primary-50 text-primary-600 text-xs font-semibold rounded-full uppercase tracking-wider mb-3">Untuk Pembeli</span>
            <h2 class="font-heading text-3xl sm:text-4xl font-bold text-surface-900">Cara Berbelanja</h2>
            <p class="text-surface-500 mt-3 max-w-xl mx-auto">5 langkah mudah dari cari produk sampai pesanan tiba di tangan Anda.</p>
        </div>

        <div class="relative">
            {{-- Connector line desktop --}}
            <div class="hidden lg:block absolute top-14 left-[10%] right-[10%] h-0.5 bg-gradient-to-r from-primary-100 via-primary-300 to-primary-100"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-4 relative">

                @php
                $buySteps = [
                    ['num'=>'1','title'=>'Cari Produk','desc'=>'Jelajahi kategori atau ketik nama produk di kotak pencarian untuk temukan produk UMKM favorit Anda.','icon'=>'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z','color'=>'primary'],
                    ['num'=>'2','title'=>'Tambah ke Keranjang','desc'=>'Pilih produk yang Anda inginkan, tentukan jumlahnya, lalu klik "Tambah ke Keranjang".','icon'=>'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z','color'=>'primary'],
                    ['num'=>'3','title'=>'Checkout','desc'=>'Buka keranjang, pilih produk yang ingin dibeli, isi alamat pengiriman (wilayah Lampung Barat), lalu konfirmasi pesanan.','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4','color'=>'primary'],
                    ['num'=>'4','title'=>'Bayar','desc'=>'Pilih metode bayar: Virtual Account (BCA, BRI, BNI, Mandiri, BSI), QRIS, atau E-Wallet (GoPay, DANA, OVO, ShopeePay). Pembayaran dikonfirmasi otomatis.','icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','color'=>'accent'],
                    ['num'=>'5','title'=>'Pesanan Dikirim','desc'=>'Seller memproses dan mengirim pesanan. Pantau status real-time di "Pesanan Saya" dan konfirmasi setelah barang diterima.','icon'=>'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4','color'=>'green'],
                ];
                @endphp

                @foreach($buySteps as $step)
                    <div class="flex flex-col items-center text-center group">
                        <div class="relative mb-5">
                            <div class="w-28 h-28 rounded-2xl flex items-center justify-center shadow-sm border-2 transition-all
                                {{ $step['color'] === 'accent' ? 'bg-accent-50 border-accent-100 group-hover:border-accent-400 group-hover:bg-accent-100' : ($step['color'] === 'green' ? 'bg-green-50 border-green-100 group-hover:border-green-400 group-hover:bg-green-100' : 'bg-primary-50 border-primary-100 group-hover:border-primary-400 group-hover:bg-primary-100') }}">
                                <svg class="w-12 h-12 {{ $step['color'] === 'accent' ? 'text-accent-600' : ($step['color'] === 'green' ? 'text-green-500' : 'text-primary-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $step['icon'] }}"/>
                                </svg>
                            </div>
                            <span class="absolute -top-2 -right-2 w-7 h-7 rounded-full text-white text-xs font-bold flex items-center justify-center shadow
                                {{ $step['color'] === 'accent' ? 'bg-accent-500 text-primary-900' : ($step['color'] === 'green' ? 'bg-green-500' : 'bg-primary-500') }}">
                                {{ $step['num'] }}
                            </span>
                        </div>
                        <h3 class="font-heading font-bold text-surface-900 text-base mb-1">{{ $step['title'] }}</h3>
                        <p class="text-sm text-surface-500 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Payment methods --}}
        <div class="mt-14 p-6 bg-surface-50 rounded-2xl border border-surface-100">
            <p class="text-center text-xs font-semibold text-surface-400 uppercase tracking-wider mb-5">Metode Pembayaran yang Didukung</p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                @foreach(['BCA VA','BRI VA','BNI VA','Mandiri VA','BSI VA'] as $m)
                    <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-surface-200 rounded-xl text-sm font-semibold text-surface-700 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-blue-400 shrink-0"></span>{{ $m }}
                    </span>
                @endforeach
                <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-surface-200 rounded-xl text-sm font-semibold text-surface-700 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-purple-400 shrink-0"></span>QRIS
                </span>
                @foreach(['GoPay','DANA','OVO','ShopeePay'] as $m)
                    <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-surface-200 rounded-xl text-sm font-semibold text-surface-700 shadow-xs">
                        <span class="w-2 h-2 rounded-full bg-green-400 shrink-0"></span>{{ $m }}
                    </span>
                @endforeach
                <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-surface-200 rounded-xl text-sm font-semibold text-surface-700 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0"></span>COD (Bayar di Tempat)
                </span>
            </div>
        </div>

        <div class="mt-8 text-center">
            <a href="{{ url('/products') }}" wire:navigate class="inline-flex items-center gap-2 px-7 py-3 bg-primary-500 hover:bg-primary-600 text-white font-bold rounded-xl transition-all hover:shadow-lg hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Mulai Belanja Sekarang
            </a>
        </div>
    </div>
</section>

{{-- Divider --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <hr class="border-surface-200">
</div>

{{-- ============================================ --}}
{{-- CARA JADI SELLER --}}
{{-- ============================================ --}}
<section id="cara-jualan" class="py-20 bg-surface-50 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <span class="inline-block px-3 py-1 bg-accent-100 text-accent-700 text-xs font-semibold rounded-full uppercase tracking-wider mb-3">Untuk Penjual</span>
            <h2 class="font-heading text-3xl sm:text-4xl font-bold text-surface-900">Cara Jadi Seller</h2>
            <p class="text-surface-500 mt-3 max-w-xl mx-auto">Daftar gratis dan buka toko dalam hitungan menit. Jangkau lebih banyak pembeli di Lampung Barat.</p>
        </div>

        <div class="max-w-3xl mx-auto">
            @php
            $sellerSteps = [
                ['num'=>'1','title'=>'Daftar Akun Seller','desc'=>'Klik "Buka Toko Gratis" di halaman utama, isi form pendaftaran penjual: nama lengkap, email, nomor HP, nama toko, dan kata sandi.','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z','color'=>'primary'],
                ['num'=>'2','title'=>'Lengkapi Profil Toko','desc'=>'Masuk ke dashboard seller, isi nama toko, deskripsi usaha, alamat toko, upload logo dan banner agar tampilan toko lebih menarik.','icon'=>'M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72','color'=>'primary'],
                ['num'=>'3','title'=>'Daftarkan Rekening Bank','desc'=>'Masukkan data rekening bank (nama bank, nomor rekening, nama pemilik) untuk menerima pencairan dana hasil penjualan.','icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','color'=>'primary'],
                ['num'=>'4','title'=>'Tunggu Verifikasi Admin','desc'=>'Tim admin memverifikasi data rekening bank Anda dalam 1–2 hari kerja. Anda akan mendapat notifikasi setelah disetujui.','icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','color'=>'accent'],
                ['num'=>'5','title'=>'Tambah Produk & Mulai Jualan!','desc'=>'Toko sudah aktif! Upload produk Anda: foto, nama, deskripsi, harga, dan stok. Pesanan akan masuk otomatis dan Anda tinggal proses.','icon'=>'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z','color'=>'green'],
            ];
            @endphp

            <div class="space-y-0">
                @foreach($sellerSteps as $step)
                    <div class="flex gap-6 {{ !$loop->last ? 'pb-8' : '' }}">
                        {{-- Icon + vertical line --}}
                        <div class="flex flex-col items-center shrink-0">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-sm border-2
                                {{ $step['color'] === 'accent' ? 'bg-accent-50 border-accent-200' : ($step['color'] === 'green' ? 'bg-green-50 border-green-200' : 'bg-primary-50 border-primary-200') }}">
                                <svg class="w-8 h-8 {{ $step['color'] === 'accent' ? 'text-accent-600' : ($step['color'] === 'green' ? 'text-green-500' : 'text-primary-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $step['icon'] }}"/>
                                </svg>
                            </div>
                            @if(!$loop->last)
                                <div class="w-0.5 flex-1 mt-3 min-h-6 bg-surface-200"></div>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="pt-3 {{ !$loop->last ? 'pb-2' : '' }}">
                            <div class="flex items-center gap-3 mb-1.5">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary-500 text-white text-xs font-bold shrink-0">{{ $step['num'] }}</span>
                                <h3 class="font-heading font-bold text-surface-900 text-lg">{{ $step['title'] }}</h3>
                            </div>
                            <p class="text-surface-500 text-sm leading-relaxed ml-9">{{ $step['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- CTA Box --}}
            <div class="mt-12 p-8 bg-primary-900 rounded-2xl text-center">
                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-accent-400/20 flex items-center justify-center">
                    <svg class="w-7 h-7 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72"/></svg>
                </div>
                <p class="text-white font-heading font-bold text-xl mb-2">Siap Buka Toko?</p>
                <p class="text-primary-200 text-sm mb-6">Bergabunglah bersama 30+ UMKM yang sudah berjualan di platform kami. Gratis, tanpa biaya pendaftaran.</p>
                <a href="{{ url('/register-seller') }}" wire:navigate class="inline-flex items-center gap-2 px-7 py-3 bg-accent-400 hover:bg-accent-300 text-primary-900 font-bold rounded-xl transition-all hover:shadow-lg hover:-translate-y-0.5">
                    Daftar Jadi Seller Sekarang
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
