# 📋 LAPORAN QA AUDIT & PENGUJIAN - UMKM AIR HITAM

**Peran:** Senior QA & Security Engineer  
**Tanggal Pengujian:** 8 Juni 2026  
**Total Pengujian:** 29 Test Cases (109 Assertions)  
**Hasil Akhir:** 💯 **100% LULUS (PASS)**

---

## 🔒 1. Kategori: Keamanan & Kontrol Akses (Security & Access Control)

| No | Fitur yang Diuji | Skenario Pengujian | Hasil yang Diharapkan | Hasil yang Muncul | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| 1 | Halaman Publik (Guest) | Mengakses beranda, daftar produk, detail produk, daftar toko, dan profil toko yang berstatus approved. | Akses diizinkan (HTTP 200). | Berhasil mengakses semua halaman publik. | **PASS** |
| 2 | Penyembunyian Toko Non-Approved | Membuka beranda, katalog produk/toko, detail toko, dan detail produk milik toko yang berstatus pending/rejected. | Toko & produk tidak muncul di publik (homepage & katalog), dan akses langsung ke detail mengembalikan status 404. | Toko/produk non-approved tersembunyi sepenuhnya dan direct access memicu HTTP 404. | **PASS** |
| 3 | Proteksi Akses Guest | Mengakses keranjang, checkout, dashboard customer, dashboard seller, dan dashboard admin tanpa login. | Diarahkan otomatis ke halaman login (Redirect ke `/login`). | Terbaca redirect 302 ke `/login`. | **PASS** |
| 4 | Hak Akses Customer | Mengakses dashboard & pesanan customer. | Akses diizinkan (HTTP 200). | Berhasil mengakses halaman khusus customer. | **PASS** |
| 5 | Pembatasan Peran Customer | Mengakses dashboard seller atau dashboard admin sebagai customer. | Akses ditolak (HTTP 403). | Mengembalikan status 403 (Forbidden). | **PASS** |
| 6 | Login Seller Pending | Login sebagai seller dengan status toko "pending". | Diarahkan ke dashboard khusus seller pending (menampilkan info review toko). | Menampilkan dashboard pending dengan status kuning. | **PASS** |
| 7 | Akses Fitur Seller Pending | Mengakses rute `/seller/products`, `/seller/orders`, `/seller/reports`, `/seller/reports/pdf`, dan `/seller/profile` sebagai seller pending. | Diarahkan kembali ke dashboard seller (Redirect 302). | Otomatis di-redirect kembali ke dashboard seller pending. | **PASS** |
| 8 | Login Seller Ditolak | Login sebagai seller dengan status toko "rejected". | Otomatis di-logout dari sistem dan diarahkan ke halaman login dengan pesan kesalahan. | Redirect 302 ke `/login` dan session dinonaktifkan. | **PASS** |
| 9 | Hak Akses Seller Approved | Mengakses dashboard seller, produk, pesanan, laporan, dan profil sebagai seller approved. | Akses diizinkan (HTTP 200) dengan fitur penuh. | Berhasil mengakses semua halaman seller. | **PASS** |
| 10 | Hak Akses Admin | Mengakses dashboard admin, manajemen toko, manajemen kategori, dan laporan platform. | Akses diizinkan (HTTP 200) dengan menu penuh. | Berhasil mengakses halaman administrator. | **PASS** |

---

## 🛠️ 2. Kategori: Fitur & Manajemen Admin (Admin Management)

| No | Fitur yang Diuji | Skenario Pengujian | Hasil yang Diharapkan | Hasil yang Muncul | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| 11 | Persetujuan Toko (Onboarding) | Admin menyetujui pendaftaran toko baru (status pending). | Status toko berubah menjadi "approved" dan peran pengguna dikonfirmasi sebagai "seller". | Data store terupdate ke "approved" di database. | **PASS** |
| 12 | Penolakan Toko (Onboarding) | Admin menolak pendaftaran toko baru (status pending). | Status toko berubah menjadi "rejected" di database. | Data store terupdate ke "rejected" di database. | **PASS** |
| 13 | Pembuatan Kategori | Admin menambahkan kategori baru melalui panel admin. | Kategori tersimpan dengan nama dan slug yang benar (URL-friendly). | Kategori baru tersimpan di database. | **PASS** |
| 14 | Pengubahan Kategori | Admin mengubah nama kategori yang sudah ada. | Nama dan slug kategori terbarui dengan benar. | Data kategori terupdate di database. | **PASS** |
| 15 | Penghapusan Kategori Aktif | Admin menghapus kategori yang masih memiliki produk aktif. | Penghapusan dibatalkan, muncul pesan peringatan, kategori tetap ada di database. | Penghapusan ditolak karena relasi produk terdeteksi. | **PASS** |
| 16 | Penghapusan Kategori Kosong | Admin menghapus kategori yang tidak memiliki produk. | Kategori berhasil dihapus dari database. | Data kategori hilang dari database. | **PASS** |

---

## 📈 3. Kategori: Fitur & Alur Kerja Penjual (Seller Features & Workflows)

| No | Fitur yang Diuji | Skenario Pengujian | Hasil yang Diharapkan | Hasil yang Muncul | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| 17 | Tambah Produk Baru | Seller approved mengunggah produk dengan gambar dan data valid. | Produk berhasil disimpan ke database dengan nama gambar yang disamarkan (UUID). | Produk tersimpan di database dengan nama image aman. | **PASS** |
| 18 | Validasi Stok & Harga | Memasukkan harga > Rp 999 juta atau stok > 999 ribu. | Sistem menampilkan kesalahan validasi dan menolak penyimpanan. | Form menampilkan pesan error validasi input. | **PASS** |
| 19 | Penghapusan Produk | Seller menghapus produk miliknya. | Produk terhapus dari database dan file gambar terkait dihapus dari penyimpanan. | Data produk terhapus dari database. | **PASS** |
| 20 | Penentuan Ongkos Kirim | Seller menentukan biaya ongkir pada pesanan yang masuk (maksimal Rp 1 juta). | Ongkir terupdate dan total harga pesanan bertambah secara otomatis. | Ongkir terisi di database dan memicu event kalkulasi total. | **PASS** |
| 21 | Konfirmasi Pembayaran | Seller menyetujui bukti transfer dari customer. | Status pesanan berubah menjadi "diproses" dan status pembayaran menjadi "paid". | Data order terupdate ke "diproses" and "paid". | **PASS** |
| 22 | Penolakan Pembayaran | Seller menolak bukti transfer karena dana tidak cocok. | Status pesanan kembali ke "pending/unpaid", file bukti transfer lama dihapus dari storage. | Bukti transfer terhapus dan status pesanan di-reset. | **PASS** |
| 23 | Pengkinian Profil Toko | Seller mengubah profil toko dengan deskripsi mengandung tag HTML. | Deskripsi tersimpan setelah tag berbahaya (seperti `<script>`) dibersihkan. | Script XSS hilang, tag dekoratif dasar seperti `<b>` diizinkan. | **PASS** |
| 24 | Proteksi Unduh PDF Laporan | Seller pending mencoba menembak URL download laporan PDF penjualan. | Diarahkan kembali ke dashboard seller pending (Redirect 302). | Request diblokir dan di-redirect ke dashboard. | **PASS** |

---

## 🛒 4. Kategori: Fitur & Alur Kerja Pembeli (Customer Workflows & Cart)

| No | Fitur yang Diuji | Skenario Pengujian | Hasil yang Diharapkan | Hasil yang Muncul | Status |
| :--- | :--- | :--- | :--- | :--- | :---: |
| 25 | Tambah ke Keranjang Belanja | Customer menambahkan produk dari toko yang aktif/approved ke keranjang. | Produk berhasil ditambahkan dengan jumlah (qty) yang sesuai. | Keranjang terisi item baru di database. | **PASS** |
| 26 | Blokir Keranjang Toko Pending | Customer mencoba menambahkan produk dari toko pending via aksi Livewire langsung. | Sistem melempar kesalahan 404 (ModelNotFoundException) karena produk dari toko pending tidak terdaftar secara publik. | Request dibatalkan dengan Exception ModelNotFound. | **PASS** |
| 27 | Penyaringan Produk di Keranjang | Membuka halaman keranjang yang berisi produk dari toko approved dan toko pending. | Produk dari toko pending secara otomatis disembunyikan dan diabaikan dari keranjang belanja. | Keranjang hanya menampilkan produk dari toko approved. | **PASS** |
| 28 | Checkout dengan Stok Cukup | Melakukan checkout pesanan dengan jumlah di bawah stok produk yang tersedia. | Pesanan terbuat di database dan stok produk terpotong (atomic decrement). | Order terbentuk dan stok terpotong secara aman. | **PASS** |
| 29 | Checkout Stok Kurang (Race) | Melakukan checkout pesanan dengan jumlah melebihi stok produk. | Transaksi di-rollback, pesanan batal terbuat, stok produk tidak berubah, pesan error ditampilkan. | Database aman karena transaction rollback bekerja dengan baik. | **PASS** |
| 30 | Unggah Bukti Pembayaran | Customer mengunggah bukti transfer (JPEG/PNG/WebP maks 2MB) pada pesanan yang sudah diberi ongkir. | Bukti transfer tersimpan dengan UUID, status pesanan berubah menjadi "menunggu_validasi". | Gambar bukti transfer terunggah dan status pesanan terupdate. | **PASS** |
| 31 | Pembatalan Pesanan | Customer membatalkan pesanan berstatus pending/menunggu_validasi. | Status pesanan menjadi "dibatalkan", stok barang dikembalikan secara utuh ke database. | Pesanan dibatalkan dan stok dikembalikan. | **PASS** |
| 32 | Konfirmasi Barang Diterima | Customer mengonfirmasi penerimaan barang pada pesanan yang telah dikirim (COD/Transfer). | Status pesanan menjadi "selesai" dan status pembayaran menjadi "paid". | Status order terupdate menjadi selesai dan lunas. | **PASS** |
