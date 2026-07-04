# 📋 SKENARIO PENGUJIAN MANUAL (MANUAL TESTING SCENARIOS)
## UMKM Air Hitam Marketplace

Dokumen ini berisi skenario pengujian manual untuk memvalidasi fitur-fitur utama dan proteksi keamanan pada aplikasi web UMKM Air Hitam. 

### Petunjuk Pengujian:
1. Jalankan aplikasi web di lingkungan lokal atau staging.
2. Lakukan pengujian langkah demi langkah sesuai kolom **Fitur yang Diuji**.
3. Tuliskan hasil yang Anda temui pada kolom **Hasil yang Keluar**.
4. Isi kolom **Status** dengan:
   - **PASS** jika hasil yang keluar sesuai dengan hasil yang diharapkan.
   - **FAIL** jika hasil yang keluar tidak sesuai atau terdapat error/bug.

---

### 🔒 1. Kategori: Autentikasi & Hak Akses (Authentication & Access Control)

| No | Fitur yang Diuji | Hasil yang Diharapkan | Hasil yang Keluar | Status |
| :---: | :--- | :--- | :--- | :---: |
| **1** | **Akses Halaman Publik (Guest)**<br>Mengakses halaman beranda, katalog produk, detail produk, daftar toko, dan profil toko yang berstatus *Approved* tanpa login. | Halaman terbuka dengan sukses (HTTP 200) dan semua data publik tampil dengan benar. | | |
| **2** | **Penyembunyian Toko & Produk Non-Approved**<br>Mencoba mengakses langsung URL detail toko atau detail produk milik toko yang statusnya masih *Pending* atau *Rejected*. | Sistem tidak menampilkan toko/produk di katalog publik, dan akses langsung ke URL mengembalikan error 404 (Not Found). | | |
| **3** | **Proteksi Halaman Terotentikasi**<br>Mencoba mengakses halaman keranjang (`/cart`), checkout, dashboard customer, dashboard seller, atau dashboard admin tanpa melakukan login terlebih dahulu. | Pengguna secara otomatis diarahkan (redirect) kembali ke halaman login (`/login`). | | |
| **4** | **Hak Akses Dashboard Customer**<br>Login menggunakan akun dengan peran Customer dan mencoba mengakses dashboard customer serta halaman riwayat pesanannya sendiri. | Akses diizinkan (HTTP 200) dan halaman dashboard customer terbuka dengan benar. | | |
| **5** | **Pembatasan Otorisasi Customer**<br>Mencoba mengakses rute dashboard seller (`/seller/dashboard`) atau dashboard admin (`/admin/dashboard`) menggunakan akun Customer yang sedang login. | Akses ditolak dan sistem menampilkan halaman error 403 (Forbidden). | | |
| **6** | **Login Seller Berstatus Pending**<br>Login menggunakan akun Seller yang pendaftaran tokonya masih berstatus *Pending*. | Pengguna berhasil login tetapi otomatis diarahkan ke dashboard khusus seller pending (menampilkan pesan bahwa toko sedang dalam peninjauan). | | |
| **7** | **Restriksi Fitur Seller Pending**<br>Mencoba mengakses rute fitur penjual aktif (seperti `/seller/products`, `/seller/orders`, `/seller/reports`, `/seller/profile`) menggunakan akun seller berstatus *Pending*. | Akses diblokir dan pengguna diarahkan kembali ke dashboard pending (Redirect 302). | | |
| **8** | **Login Seller Berstatus Rejected**<br>Login menggunakan akun Seller yang pendaftaran tokonya telah ditolak (*Rejected*) oleh admin. | Sistem menolak login, otomatis melakukan logout, dan mengarahkan kembali ke halaman login dengan pesan kesalahan bahwa toko ditolak. | | |
| **9** | **Hak Akses Seller Approved**<br>Login menggunakan akun Seller berstatus *Approved* dan mengakses dashboard seller, kelola produk, kelola pesanan, laporan penjualan, dan profil toko. | Akses diizinkan (HTTP 200) dan semua menu kelola toko dapat diakses secara penuh. | | |
| **10** | **Hak Akses Administrator**<br>Login menggunakan akun Admin dan mengakses dashboard admin, manajemen toko (persetujuan onboarding), manajemen kategori, dan laporan platform. | Akses diizinkan (HTTP 200) dan seluruh panel kontrol admin terbuka dengan benar. | | |

---

### 🛠️ 2. Kategori: Fitur & Manajemen Admin (Admin Management)

| No | Fitur yang Diuji | Hasil yang Diharapkan | Hasil yang Keluar | Status |
| :---: | :--- | :--- | :--- | :---: |
| **11** | **Persetujuan Pendaftaran Toko (Approve Seller)**<br>Admin mengklik tombol "Approve" pada salah satu pendaftaran toko baru yang berstatus *Pending*. | Status toko berubah menjadi *Approved*, peran akun pengguna dikonfirmasi sebagai seller aktif, dan seller terkait sekarang bisa login ke dashboard penuh. | | |
| **12** | **Penolakan Pendaftaran Toko (Reject Seller)**<br>Admin mengklik tombol "Reject" pada salah satu pendaftaran toko baru yang berstatus *Pending*. | Status toko berubah menjadi *Rejected* di database dan seller tersebut tidak mendapatkan hak akses dashboard penuh. | | |
| **13** | **Pembuatan Kategori Produk**<br>Admin menambahkan kategori baru melalui panel admin dengan mengisi nama kategori. | Kategori baru berhasil disimpan ke database, dan slug kategori yang ramah URL (URL-friendly) terbuat otomatis. | | |
| **14** | **Pengubahan Kategori Produk**<br>Admin mengubah nama kategori yang sudah ada melalui form edit kategori. | Nama kategori berhasil diperbarui di database dan slug-nya ikut ter-update secara otomatis sesuai nama baru. | | |
| **15** | **Penghapusan Kategori Aktif (Memiliki Produk)**<br>Admin mencoba menghapus kategori yang masih digunakan oleh produk aktif di marketplace. | Penghapusan ditolak/dibatalkan, muncul pesan peringatan/error, dan kategori tersebut tetap aman di database. | | |
| **16** | **Penghapusan Kategori Kosong**<br>Admin menghapus kategori produk yang sama sekali tidak memiliki produk terkait. | Kategori berhasil terhapus dari database dengan sukses. | | |

---

### 📈 3. Kategori: Fitur & Alur Kerja Penjual (Seller Features)

| No | Fitur yang Diuji | Hasil yang Diharapkan | Hasil yang Keluar | Status |
| :---: | :--- | :--- | :--- | :---: |
| **17** | **Tambah Produk Baru dengan Gambar**<br>Penjual (Approved) mengunggah produk baru dengan data lengkap (nama, deskripsi, harga, stok, kategori) dan gambar produk valid. | Produk berhasil disimpan, dan file gambar disimpan di storage dengan nama file acak yang aman (UUID) untuk mencegah path traversal. | | |
| **18** | **Validasi Batas Atas Input Harga & Stok**<br>Penjual mencoba memasukkan harga ekstrem (> Rp 999.999.999) atau stok ekstrem (> 999.999) saat menambah/mengedit produk. | Form menolak penyimpanan dan menampilkan pesan error validasi input harga/stok melebihi batas maksimum. | | |
| **19** | **Penghapusan Produk**<br>Penjual menghapus salah satu produk aktif miliknya dari daftar produk. | Produk berhasil terhapus dari database, dan file gambar produk terkait di storage otomatis ikut terhapus. | | |
| **20** | **Penentuan Biaya Ongkos Kirim**<br>Penjual menginput biaya ongkir pada pesanan masuk yang statusnya belum ditentukan ongkirnya (maksimal Rp 1.000.000). | Biaya ongkir tersimpan di database, dan total harga pesanan terakumulasi secara otomatis (Harga Produk + Ongkir). | | |
| **21** | **Validasi Batas Maksimum Ongkos Kirim**<br>Penjual menginput biaya ongkir melebihi batas maksimum Rp 1.000.000 (misalnya Rp 1.500.000). | Sistem memblokir input dan menampilkan pesan validasi bahwa ongkos kirim tidak boleh melebihi Rp 1.000.000. | | |
| **22** | **Konfirmasi Bukti Transfer (Terima Pembayaran)**<br>Penjual memeriksa dan menyetujui bukti transfer yang diunggah oleh pembeli. | Status pesanan berubah menjadi "Diproses" dan status pembayaran berubah menjadi "Paid". | | |
| **23** | **Penolakan Bukti Transfer**<br>Penjual menolak bukti transfer karena gambar blur atau nominal transfer tidak sesuai. | Status pesanan kembali ke "Pending/Unpaid", file bukti transfer yang ditolak terhapus dari storage untuk menghemat ruang. | | |
| **24** | **Sanitisasi Deskripsi Toko (Anti-XSS)**<br>Penjual mengedit profil toko dan mengisi deskripsi toko dengan tag HTML berbahaya seperti `<script>alert('XSS')</script>`. | Deskripsi berhasil disimpan, namun tag `<script>` berbahaya dibersihkan/dihapus otomatis sehingga tidak memicu pop-up script saat diakses publik. | | |

---

### 🛒 4. Kategori: Fitur & Alur Kerja Pembeli (Customer Workflows & Cart)

| No | Fitur yang Diuji | Hasil yang Diharapkan | Hasil yang Keluar | Status |
| :---: | :--- | :--- | :--- | :---: |
| **25** | **Tambah ke Keranjang Belanja**<br>Customer menambahkan produk dari toko approved/aktif ke dalam keranjang belanja. | Item berhasil masuk ke keranjang belanja dengan kuantitas yang sesuai. | | |
| **26** | **Blokir Tambah Keranjang Toko Pending (Direct Livewire Action Bypass)**<br>Mencoba memicu aksi Livewire secara langsung menggunakan ID produk milik toko pending untuk dimasukkan ke keranjang. | Request gagal/ditolak oleh sistem dan mengembalikan ModelNotFoundException (404). | | |
| **27** | **Filter Keranjang Belanja Aktif**<br>Membuka keranjang belanja yang berisi produk dari toko approved dan toko yang statusnya baru saja diubah admin menjadi pending/rejected. | Produk dari toko pending/rejected otomatis disembunyikan/dihapus dari keranjang belanja yang ditampilkan. | | |
| **28** | **Checkout dengan Stok Cukup**<br>Customer melakukan checkout pesanan dengan jumlah produk yang dipesan kurang dari atau sama dengan stok yang tersedia. | Pesanan berhasil dibuat di database dan stok produk terpotong (decrement) secara aman dan akurat. | | |
| **29** | **Checkout Melebihi Stok (Race Condition Protection)**<br>Mencoba membeli produk melebihi stok yang tersedia, atau dua pengguna melakukan checkout produk sisa 1 secara bersamaan. | Salah satu transaksi/checkout dibatalkan (rollback), pesanan tidak terbuat, stok produk tidak menjadi negatif, dan tampil pesan error "Stok tidak mencukupi". | | |
| **30** | **Unggah Bukti Pembayaran**<br>Customer mengunggah bukti transfer (file JPEG/PNG/WebP maks 2MB) pada pesanan yang telah diisi ongkirnya oleh penjual. | File bukti transfer berhasil terunggah dengan nama UUID aman, dan status pesanan berubah menjadi "Menunggu Validasi". | | |
| **31** | **Pembatalan Pesanan**<br>Customer mengklik tombol batalkan pesanan pada pesanan yang masih berstatus "Pending" atau "Menunggu Validasi". | Status pesanan berubah menjadi "Dibatalkan", dan jumlah stok produk yang dipesan dikembalikan secara utuh ke database. | | |
| **32** | **Konfirmasi Pesanan Diterima**<br>Customer mengklik tombol konfirmasi penerimaan barang pada pesanan yang telah dikirim penjual. | Status pesanan terupdate menjadi "Selesai" dan status pembayaran dikonfirmasi sebagai "Paid". | | |
