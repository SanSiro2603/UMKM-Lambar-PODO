@extends('layouts.app')

@section('title', 'Panduan Seller & Customer')
@section('meta_description', 'Panduan lengkap menggunakan UMKM Lampung Barat untuk seller dan customer, mulai dari pendaftaran hingga pesanan selesai.')

@section('content')

@php
    $requestedTab = request()->query('tab', 'seller');
    $initialTab = in_array($requestedTab, ['seller', 'customer'], true) ? $requestedTab : 'seller';

    $guides = [
        'seller' => [
            'eyebrow' => 'Panduan untuk Pelaku UMKM',
            'title' => 'Mulai Berjualan sebagai Seller',
            'description' => 'Ikuti enam tahap berikut untuk mendaftarkan usaha, menampilkan produk, memproses pesanan, dan memantau hasil penjualan.',
            'primaryCta' => ['label' => 'Daftar Jadi Seller', 'url' => url('/register-seller')],
            'secondaryCta' => ['label' => 'Masuk ke Akun', 'url' => url('/login')],
            'steps' => [
                [
                    'id' => 'seller-daftar',
                    'title' => 'Persiapkan Data & Daftar Akun Seller',
                    'summary' => 'Pendaftaran dilakukan sekali dan langsung mencakup identitas pemilik, informasi toko, alamat usaha, serta rekening pencairan.',
                    'icon' => 'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m11-11a4 4 0 11-8 0 4 4 0 018 0zm8 11v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75',
                    'items' => [
                        'Siapkan nama toko, nama pemilik, email aktif, dan nomor WhatsApp.',
                        'Pilih kecamatan dan desa/kelurahan di Kabupaten Lampung Barat, lalu isi alamat lengkap.',
                        'Tambahkan deskripsi singkat yang menjelaskan usaha dan produk unggulan.',
                        'Isi bank, nomor rekening, nama pemilik rekening, password, dan persetujuan ketentuan.',
                    ],
                    'note' => 'Pastikan nama pemilik rekening sesuai dengan data bank. Kesalahan data dapat memperlambat verifikasi dan pencairan.',
                    'cta' => ['label' => 'Buka Form Pendaftaran Seller', 'url' => url('/register-seller')],
                ],
                [
                    'id' => 'seller-verifikasi',
                    'title' => 'Tunggu Verifikasi Admin',
                    'summary' => 'Setelah formulir dikirim, toko dan rekening masuk ke antrean pemeriksaan admin sebelum dapat berjualan.',
                    'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                    'items' => [
                        'Status awal toko dan rekening adalah Pending.',
                        'Proses peninjauan ditargetkan selesai maksimal 1×24 jam.',
                        'Setelah disetujui, seller dapat masuk dan mengakses seluruh menu dashboard.',
                        'Jika ditolak, baca alasan yang diberikan dan hubungi admin untuk memperbaiki data.',
                    ],
                    'note' => 'Produk belum dapat dipublikasikan selama toko masih berstatus Pending.',
                ],
                [
                    'id' => 'seller-profil',
                    'title' => 'Masuk & Lengkapi Profil Toko',
                    'summary' => 'Profil yang lengkap membantu customer mengenali identitas dan kualitas usaha Anda.',
                    'icon' => 'M3 10h18M5 6h14l1 4H4l1-4zm1 4h12v7H6v-7zm3 0v7m6-7v7',
                    'items' => [
                        'Masuk memakai email dan password yang didaftarkan.',
                        'Unggah logo toko yang jelas dan banner dengan komposisi horizontal.',
                        'Perbarui deskripsi serta alamat operasional jika ada perubahan.',
                        'Periksa status verifikasi rekening sebelum menerima transaksi online.',
                    ],
                    'note' => 'Gunakan foto identitas visual yang konsisten agar toko mudah dikenali di katalog.',
                    'cta' => ['label' => 'Masuk ke Akun Seller', 'url' => url('/login')],
                ],
                [
                    'id' => 'seller-produk',
                    'title' => 'Tambahkan Produk',
                    'summary' => 'Masukkan informasi yang lengkap agar produk mudah ditemukan dan customer memahami apa yang akan dibeli.',
                    'icon' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0l-8 5-8-5m16 0l-8 5m0 0l-8-5m8 5v3',
                    'items' => [
                        'Unggah foto JPEG, PNG, atau WebP maksimal 2 MB dengan ukuran minimal 300×300 piksel.',
                        'Isi nama produk, pilih kategori yang sesuai, dan tulis deskripsi minimal 10 karakter.',
                        'Masukkan harga dalam rupiah tanpa desimal serta jumlah stok yang tersedia.',
                        'Simpan produk, lalu periksa tampilannya pada katalog toko.',
                    ],
                    'note' => 'Gunakan foto terang dengan latar bersih dan jangan menampilkan informasi yang berbeda dari produk sebenarnya.',
                ],
                [
                    'id' => 'seller-pesanan',
                    'title' => 'Kelola Pesanan & Pembayaran',
                    'summary' => 'Pesanan dari customer muncul di menu Pesanan Masuk dengan detail produk, ongkir, alamat, dan metode pembayaran.',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                    'items' => [
                        'Pesanan online diproses setelah pembayaran terkonfirmasi.',
                        'Pesanan COD dapat langsung disiapkan dan pembayarannya ditagih saat pengantaran.',
                        'Cocokkan produk, jumlah, alamat tujuan, nomor customer, serta ongkos kirim.',
                        'Kemas produk dengan aman sebelum mengatur kurir.',
                    ],
                    'note' => 'Jangan mengirim pesanan online yang masih berstatus Menunggu Pembayaran.',
                ],
                [
                    'id' => 'seller-pengiriman',
                    'title' => 'Atur Kurir, Tracking & Laporan',
                    'summary' => 'Seller menghubungkan pesanan dengan kurir, membagikan akses pengantaran, lalu memantau penyelesaian transaksi.',
                    'icon' => 'M9 17a2 2 0 104 0m-4 0H7a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v10m-6 2h4m2 0h2a2 2 0 002-2v-3.586a1 1 0 00-.293-.707l-2.414-2.414A1 1 0 0015.586 8H15',
                    'items' => [
                        'Masukkan nama kurir dan nomor WhatsApp yang aktif.',
                        'Kirim tautan akses sekali pakai kepada kurir melalui WhatsApp.',
                        'Pantau posisi kurir dan status pengiriman dari detail pesanan.',
                        'Setelah selesai, tinjau pencairan transaksi online dan rekap penjualan pada menu Laporan.',
                    ],
                    'note' => 'Tautan kurir hanya digunakan untuk satu pesanan. Jangan membagikannya kepada pihak lain.',
                ],
            ],
        ],
        'customer' => [
            'eyebrow' => 'Panduan untuk Pembeli',
            'title' => 'Belanja sebagai Customer',
            'description' => 'Mulai dari membuat akun, mencari produk lokal, menghitung ongkir, hingga menerima dan memberi penilaian pada pesanan.',
            'primaryCta' => ['label' => 'Daftar Customer', 'url' => url('/register')],
            'secondaryCta' => ['label' => 'Jelajahi Produk', 'url' => url('/products')],
            'steps' => [
                [
                    'id' => 'customer-daftar',
                    'title' => 'Daftar Akun Customer',
                    'summary' => 'Akun customer menyimpan identitas dan alamat agar proses checkout berikutnya lebih cepat.',
                    'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2 5a4 4 0 00-8 0m8 0a4 4 0 01-8 0m8 0H5m8-8a4 4 0 11-8 0 4 4 0 018 0z',
                    'items' => [
                        'Isi nama lengkap, email, dan nomor telepon yang dapat dihubungi kurir.',
                        'Pilih kecamatan dan desa/kelurahan di Kabupaten Lampung Barat.',
                        'Lengkapi nama jalan, nomor rumah, RT/RW, dan patokan lokasi.',
                        'Buat password minimal 8 karakter, konfirmasi password, dan setujui ketentuan.',
                    ],
                    'note' => 'Alamat pendaftaran digunakan sebagai alamat awal checkout dan dapat diperbarui dari dashboard customer.',
                    'cta' => ['label' => 'Buat Akun Customer', 'url' => url('/register')],
                ],
                [
                    'id' => 'customer-cari',
                    'title' => 'Cari & Pelajari Produk',
                    'summary' => 'Gunakan beberapa jalur penelusuran untuk menemukan produk yang sesuai dengan kebutuhan.',
                    'icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
                    'items' => [
                        'Ketik nama produk atau toko melalui pencarian di bagian atas halaman.',
                        'Gunakan Kategori untuk mempersempit jenis produk.',
                        'Buka Toko UMKM untuk melihat seluruh katalog dari seller tertentu.',
                        'Periksa deskripsi, harga, stok, identitas toko, dan rating sebelum membeli.',
                    ],
                    'note' => 'Pastikan stok masih tersedia dan baca deskripsi produk secara lengkap.',
                    'cta' => ['label' => 'Lihat Katalog Produk', 'url' => url('/products')],
                ],
                [
                    'id' => 'customer-keranjang',
                    'title' => 'Gunakan Keranjang atau Beli Sekarang',
                    'summary' => 'Pilih jalur pembelian berdasarkan apakah Anda ingin membandingkan beberapa produk atau langsung checkout.',
                    'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                    'items' => [
                        'Atur jumlah barang tanpa melebihi stok yang tersedia.',
                        'Pilih Tambah ke Keranjang untuk menyimpan dan membandingkan beberapa produk.',
                        'Centang hanya item yang ingin diproses ketika membuka keranjang.',
                        'Gunakan Beli Sekarang jika ingin langsung menuju checkout satu produk.',
                    ],
                    'note' => 'Produk dari beberapa toko akan dikelompokkan menjadi pesanan terpisah saat checkout.',
                ],
                [
                    'id' => 'customer-checkout',
                    'title' => 'Periksa Checkout & Ongkos Kirim',
                    'summary' => 'Checkout menampilkan pesanan per toko dan menghitung ongkir otomatis berdasarkan kecamatan toko dan customer.',
                    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
                    'items' => [
                        'Pastikan alamat pengiriman sudah lengkap dan berada di Kabupaten Lampung Barat.',
                        'Periksa nomor HP agar kurir dapat menghubungi Anda.',
                        'Tinjau subtotal, zona ongkir, biaya kirim, dan total setiap toko.',
                        'Pilih metode pembayaran untuk masing-masing kelompok toko sebelum konfirmasi.',
                    ],
                    'note' => 'Satu checkout dapat menghasilkan lebih dari satu nomor pesanan jika produk berasal dari toko berbeda.',
                ],
                [
                    'id' => 'customer-pembayaran',
                    'title' => 'Selesaikan Pembayaran',
                    'summary' => 'Customer dapat menggunakan pembayaran online atau memilih COD sesuai kebutuhan.',
                    'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                    'items' => [
                        'Pembayaran online mendukung kanal yang tersedia seperti Virtual Account, QRIS, dan dompet digital.',
                        'Ikuti halaman pembayaran sampai sistem mengonfirmasi transaksi.',
                        'Untuk COD, siapkan uang sesuai total pesanan ketika kurir tiba.',
                        'Pantau label Menunggu Pembayaran, Dibayar, Dikirim, Selesai, atau Dibatalkan.',
                    ],
                    'note' => 'Jika pembayaran online belum berubah setelah dibayar, buka kembali detail pesanan untuk memeriksa status terbaru.',
                ],
                [
                    'id' => 'customer-terima',
                    'title' => 'Pantau, Terima & Beri Rating',
                    'summary' => 'Seluruh perkembangan pesanan tersedia di menu Pesanan Saya sampai transaksi dinyatakan selesai.',
                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    'items' => [
                        'Buka detail pesanan untuk melihat timeline status dan informasi kurir.',
                        'Saat pengiriman aktif, pantau posisi kurir melalui peta tracking.',
                        'Klik Pesanan Diterima hanya setelah barang benar-benar sampai dan diperiksa.',
                        'Berikan rating 1–5 bintang dan komentar untuk setiap produk yang dibeli.',
                    ],
                    'note' => 'Hubungi seller atau admin sebelum menyelesaikan pesanan jika barang tidak sesuai.',
                ],
            ],
        ],
    ];
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-br from-primary-900 via-primary-700 to-primary-500">
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute -top-32 -right-20 w-96 h-96 rounded-full bg-accent-400/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-24 w-96 h-96 rounded-full bg-white/5 blur-3xl"></div>
        <svg class="absolute bottom-0 left-0 w-full h-20 text-surface-50" viewBox="0 0 1440 120" preserveAspectRatio="none" fill="currentColor">
            <path d="M0,80L60,72C120,64,240,48,360,50.7C480,53,600,75,720,77.3C840,80,960,64,1080,56C1200,48,1320,48,1380,48L1440,48L1440,120L0,120Z"/>
        </svg>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-32 sm:pt-14 sm:pb-36">
        <nav aria-label="Breadcrumb" class="flex items-center justify-center gap-2 text-sm text-primary-200 mb-7">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-white transition-colors">Beranda</a>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-white font-medium" aria-current="page">Panduan</span>
        </nav>

        <div class="max-w-3xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-primary-100 text-xs font-bold uppercase tracking-wider">
                <svg class="w-4 h-4 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"/></svg>
                Pusat Panduan
            </span>
            <h1 class="mt-5 font-heading text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
                Panduan Menggunakan <span class="text-accent-400">UMKM Lampung Barat</span>
            </h1>
            <p class="mt-5 text-base sm:text-lg text-primary-100 leading-relaxed max-w-2xl mx-auto">
                Pelajari seluruh alur sebagai seller maupun customer melalui langkah yang ringkas, rinci, dan sesuai dengan fitur aplikasi.
            </p>
        </div>
    </div>
</section>

<div class="bg-surface-50" data-initial-tab="{{ $initialTab }}"
     x-data="{
        activeTab: @js($initialTab),
        tabs: ['seller', 'customer'],
        selectTab(tab, focusTab = false) {
            this.activeTab = tab;
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.pathname + url.search + url.hash);
            if (focusTab) {
                this.$nextTick(() => document.getElementById('panduan-tab-' + tab)?.focus());
            }
        },
        moveTab(direction) {
            const currentIndex = this.tabs.indexOf(this.activeTab);
            const nextIndex = (currentIndex + direction + this.tabs.length) % this.tabs.length;
            this.selectTab(this.tabs[nextIndex], true);
        }
     }">
    {{-- Accessible tab selector --}}
    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16">
        <div role="tablist" aria-label="Pilih jenis panduan" class="grid grid-cols-2 gap-3 sm:gap-4 rounded-3xl bg-white p-3 shadow-card-hover border border-surface-200">
            <button type="button"
                    id="panduan-tab-seller"
                    role="tab"
                    aria-controls="panduan-panel-seller"
                    :aria-selected="activeTab === 'seller'"
                    :tabindex="activeTab === 'seller' ? 0 : -1"
                    @click="selectTab('seller')"
                    @keydown.arrow-right.prevent="moveTab(1)"
                    @keydown.arrow-left.prevent="moveTab(-1)"
                    @keydown.home.prevent="selectTab('seller', true)"
                    @keydown.end.prevent="selectTab('customer', true)"
                    class="cursor-pointer flex items-center gap-3 sm:gap-4 rounded-2xl p-3 sm:p-5 text-left transition-colors duration-200 focus-visible:ring-2 focus-visible:ring-primary-400 focus-visible:ring-offset-2"
                    :class="activeTab === 'seller' ? 'bg-primary-900 text-white' : 'bg-surface-50 text-surface-700 hover:bg-primary-50'">
                <span class="w-11 h-11 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0"
                      :class="activeTab === 'seller' ? 'bg-accent-400 text-primary-900' : 'bg-accent-100 text-accent-700'">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="block font-heading font-bold text-sm sm:text-xl">Tutorial Seller</span>
                    <span class="hidden sm:block mt-1 text-sm" :class="activeTab === 'seller' ? 'text-primary-200' : 'text-surface-500'">Daftar, kelola toko, dan kirim pesanan.</span>
                </span>
            </button>

            <button type="button"
                    id="panduan-tab-customer"
                    role="tab"
                    aria-controls="panduan-panel-customer"
                    :aria-selected="activeTab === 'customer'"
                    :tabindex="activeTab === 'customer' ? 0 : -1"
                    @click="selectTab('customer')"
                    @keydown.arrow-right.prevent="moveTab(1)"
                    @keydown.arrow-left.prevent="moveTab(-1)"
                    @keydown.home.prevent="selectTab('seller', true)"
                    @keydown.end.prevent="selectTab('customer', true)"
                    class="cursor-pointer flex items-center gap-3 sm:gap-4 rounded-2xl p-3 sm:p-5 text-left transition-colors duration-200 focus-visible:ring-2 focus-visible:ring-primary-400 focus-visible:ring-offset-2"
                    :class="activeTab === 'customer' ? 'bg-primary-900 text-white' : 'bg-surface-50 text-surface-700 hover:bg-primary-50'">
                <span class="w-11 h-11 sm:w-14 sm:h-14 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0"
                      :class="activeTab === 'customer' ? 'bg-accent-400 text-primary-900' : 'bg-primary-100 text-primary-600'">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="block font-heading font-bold text-sm sm:text-xl">Tutorial Customer</span>
                    <span class="hidden sm:block mt-1 text-sm" :class="activeTab === 'customer' ? 'text-primary-200' : 'text-surface-500'">Cari, pesan, bayar, dan terima produk.</span>
                </span>
            </button>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        @foreach($guides as $guideKey => $guide)
            @php $isSeller = $guideKey === 'seller'; @endphp
            <section id="panduan-panel-{{ $guideKey }}"
                     role="tabpanel"
                     aria-labelledby="panduan-tab-{{ $guideKey }}"
                     x-show="activeTab === '{{ $guideKey }}'"
                     x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     style="{{ $initialTab === $guideKey ? '' : 'display: none;' }}">
                <header class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-10">
                    <div class="max-w-3xl">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider {{ $isSeller ? 'bg-accent-100 text-accent-700' : 'bg-primary-100 text-primary-700' }}">
                            {{ $guide['eyebrow'] }}
                        </span>
                        <h2 class="mt-4 font-heading text-3xl sm:text-4xl font-bold text-surface-900">{{ $guide['title'] }}</h2>
                        <p class="mt-3 text-surface-600 leading-relaxed">{{ $guide['description'] }}</p>
                    </div>
                    <div class="inline-flex items-center gap-3 rounded-2xl bg-white border border-surface-200 px-5 py-3 shadow-sm shrink-0">
                        <span class="font-heading text-3xl font-extrabold text-primary-500">6</span>
                        <span class="text-sm text-surface-600 leading-tight">tahap<br>lengkap</span>
                    </div>
                </header>

                <ol class="space-y-6">
                    @foreach($guide['steps'] as $step)
                        <li id="{{ $step['id'] }}" class="scroll-mt-36">
                            <article class="rounded-3xl bg-white border border-surface-200 shadow-card overflow-hidden">
                                <div class="p-5 sm:p-7 lg:p-8">
                                    <div class="flex items-start gap-3 sm:gap-4">
                                        <span class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center font-heading text-lg font-extrabold shrink-0 {{ $isSeller ? 'bg-accent-400 text-primary-900' : 'bg-primary-500 text-white' }}">
                                            {{ $loop->iteration }}
                                        </span>
                                        <div class="min-w-0 flex-1 pt-0.5">
                                            <p class="text-xs font-bold uppercase tracking-wider {{ $isSeller ? 'text-accent-600' : 'text-primary-500' }}">Langkah {{ $loop->iteration }}</p>
                                            <h3 class="mt-1 font-heading text-xl sm:text-2xl font-bold text-surface-900">{{ $step['title'] }}</h3>
                                        </div>
                                        <span class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl flex items-center justify-center shrink-0 {{ $isSeller ? 'bg-accent-50 text-accent-700' : 'bg-primary-50 text-primary-600' }}">
                                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $step['icon'] }}"/></svg>
                                        </span>
                                    </div>

                                    <p class="mt-5 text-surface-600 leading-relaxed">{{ $step['summary'] }}</p>

                                    <ul class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-3">
                                        @foreach($step['items'] as $item)
                                            <li class="flex items-start gap-3 rounded-2xl bg-surface-50 border border-surface-100 p-4 text-sm text-surface-700 leading-relaxed">
                                                <svg class="w-5 h-5 mt-0.5 shrink-0 {{ $isSeller ? 'text-accent-600' : 'text-primary-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span>{{ $item }}</span>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                        <div class="flex items-start gap-3 rounded-2xl px-4 py-3 {{ $isSeller ? 'bg-accent-50 text-accent-800' : 'bg-primary-50 text-primary-700' }}">
                                            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <p class="text-sm leading-relaxed"><strong>Catatan:</strong> {{ $step['note'] }}</p>
                                        </div>

                                        @if(isset($step['cta']))
                                            <a href="{{ $step['cta']['url'] }}" wire:navigate class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-primary-500 hover:bg-primary-600 text-white text-sm font-bold transition-colors duration-200 shrink-0">
                                                {{ $step['cta']['label'] }}
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ol>

                <div class="mt-10 rounded-3xl bg-primary-900 p-6 sm:p-8 lg:p-10 overflow-hidden relative">
                    <div class="absolute -right-20 -top-24 w-72 h-72 rounded-full bg-accent-400/10 blur-3xl pointer-events-none" aria-hidden="true"></div>
                    <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div class="max-w-2xl">
                            <p class="font-heading text-2xl sm:text-3xl font-bold text-white">
                                {{ $isSeller ? 'Siap membawa usaha Anda ke lebih banyak pembeli?' : 'Siap berbelanja produk lokal Lampung Barat?' }}
                            </p>
                            <p class="mt-2 text-primary-200">
                                {{ $isSeller ? 'Daftarkan toko secara gratis atau masuk jika akun Anda sudah disetujui.' : 'Buat akun customer atau mulai jelajahi produk UMKM yang tersedia.' }}
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3 shrink-0">
                            <a href="{{ $guide['secondaryCta']['url'] }}" wire:navigate class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-white/20 bg-white/10 hover:bg-white/15 text-white text-sm font-bold transition-colors duration-200">
                                {{ $guide['secondaryCta']['label'] }}
                            </a>
                            <a href="{{ $guide['primaryCta']['url'] }}" wire:navigate class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-accent-400 hover:bg-accent-300 text-primary-900 text-sm font-bold transition-colors duration-200">
                                {{ $guide['primaryCta']['label'] }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        @endforeach

        <aside class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-4" aria-label="Informasi penting">
            <div class="rounded-2xl bg-white border border-surface-200 p-5">
                <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="font-heading font-bold text-surface-900">Wilayah Layanan</h3>
                <p class="mt-2 text-sm text-surface-600 leading-relaxed">Pendaftaran alamat dan pengiriman saat ini khusus Kabupaten Lampung Barat.</p>
            </div>
            <div class="rounded-2xl bg-white border border-surface-200 p-5">
                <div class="w-10 h-10 rounded-xl bg-accent-50 text-accent-700 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <h3 class="font-heading font-bold text-surface-900">Pilihan Pembayaran</h3>
                <p class="mt-2 text-sm text-surface-600 leading-relaxed">Customer dapat memilih pembayaran online atau COD.</p>
            </div>
            <div class="rounded-2xl bg-white border border-surface-200 p-5">
                <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414A7 7 0 015.05 16.95l-1.414 1.414M5.636 5.636L7.05 7.05a7 7 0 019.9 9.9l1.414 1.414M8.464 8.464l1.415 1.415m4.242 4.242l1.415 1.415M12 12h.01"/></svg>
                </div>
                <h3 class="font-heading font-bold text-surface-900">Butuh Bantuan?</h3>
                <p class="mt-2 text-sm text-surface-600 leading-relaxed">Simpan detail kendala dan kode pesanan sebelum menghubungi admin.</p>
            </div>
        </aside>
    </div>
</div>

@endsection
