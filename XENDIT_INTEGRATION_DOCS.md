# Dokumentasi Migrasi Xendit Payment Gateway — UMKM Air Hitam

## Ringkasan

Dokumen ini menjelaskan migrasi penuh dari sistem pembayaran lama (upload bukti transfer + verifikasi manual seller) ke sistem baru menggunakan Xendit Payment Gateway dengan split payment otomatis.

---

## 1. Arsitektur & Alur Baru

```
Customer Checkout → Seller input ongkir → Customer klik Bayar
    → Xendit Invoice dibuat → Redirect ke payment page Xendit
    → Customer bayar (VA/QRIS/GoPay/DANA/dll) → Xendit kirim webhook
    → Server update PAID → Disbursement otomatis ke seller
    → Seller update status pengiriman → Customer terima
```

### Status Order Baru
| Status | Deskripsi |
|--------|-----------|
| `waiting_shipping_cost` | Seller belum input ongkir |
| `waiting_payment` | Total sudah ada, customer bisa bayar |
| `paid` | Pembayaran dikonfirmasi Xendit (otomatis via webhook) |
| `shipped` | Seller kirim barang |
| `delivered` | Selesai |
| `cancelled` | Dibatalkan |

---

## 2. Yang Dihapus (Sistem Lama)

### Kolom Database (orders)
- `proof_of_transfer` — foto bukti transfer
- `payment_verified_at` — timestamp verifikasi manual
- `payment_verified_by` — FK user yang verifikasi
- `payment_rejection_reason` — alasan penolakan

### Enum Values
- `payment_status`: `verified` → dihapus
- `payment_method`: `transfer` → diganti `xendit`
- `status`: `pending`, `menunggu_validasi`, `diproses`, `dikirim`, `selesai`, `dibatalkan` → diganti

### Method di Livewire Component
- `Seller/Orders.php`: `approvePayment()`, `rejectPayment()`, `approveCodOrder()` — dihapus
- `Customer/OrderDetails.php`: `uploadProof()`, `WithFileUploads` — dihapus

### View
- Form upload bukti transfer di `order-details.blade.php` (customer) — dihapus
- Tampilan bukti + tombol approve/reject di `orders.blade.php` (seller) — dihapus
- Opsi "Transfer Langsung" di `checkout.blade.php` — diganti "Xendit (Online)"

---

## 3. Yang Ditambahkan (Sistem Baru)

### Migration (3 file baru)
| File | Keterangan |
|------|-----------|
| `2026_06_21_000001_drop_old_payment_system_from_orders.php` | Drop kolom lama + ubah enum + tambah kolom Xendit |
| `2026_06_21_000002_update_store_bank_columns.php` | Rename kolom bank di stores |
| `2026_06_21_000003_create_transactions_table.php` | Tabel transactions baru |

### Config
| File | Keterangan |
|------|-----------|
| `config/banks.php` | Daftar kode bank (BCA, BRI, BNI, Mandiri, dll) |
| `config/services.php` | Section `xendit` dengan `secret_key`, `webhook_token`, `platform_fee_percent` |
| `.env` | `XENDIT_SECRET_KEY`, `XENDIT_WEBHOOK_TOKEN`, `XENDIT_BASE_URL`, `XENDIT_PLATFORM_FEE_PERCENT` |

### Service
| File | Keterangan |
|------|-----------|
| `app/Services/XenditService.php` | Service class — pakai SDK `xendit/xendit-php` |

### Model (3 file)
| File | Keterangan |
|------|-----------|
| `app/Models/Order.php` | Tambah `xendit_invoice_id`, `xendit_invoice_url`, `paid_at`, method `canPay()`, `isPaid()` |
| `app/Models/Store.php` | Kolom `bank_verify_status`, `bank_reject_reason`, method `hasBankAccount()`, `isBankVerified()` |
| `app/Models/Transaction.php` | Model baru — relasi ke `order` & `seller` |

### Livewire Components
| File | Route | Keterangan |
|------|-------|-----------|
| `app/Livewire/Seller/BankAccount.php` | `/seller/bank` | Form rekening seller + validasi Xendit |
| `app/Livewire/Admin/BankVerification.php` | `/admin/bank-verification` | Admin approve/reject rekening |
| `app/Livewire/Seller/Orders.php` | `/seller/orders` | Seller input ongkir + ship complete |
| `app/Livewire/Customer/OrderDetails.php` | `/customer/orders/{id}` | Customer bayar via Xendit + batalkan + konfirmasi terima |
| `app/Livewire/Checkout.php` | `/checkout` | Checkout — payment method `xendit` / `cod` |

### Controller (Backend)
| File | Keterangan |
|------|-----------|
| `app/Http/Controllers/WebhookController.php` | Handle webhook Xendit (PAID/EXPIRED) + auto-disbursement |

### View
| File | Keterangan |
|------|-----------|
| `resources/views/livewire/seller/bank-account.blade.php` | Form + status rekening |
| `resources/views/livewire/admin/bank-verification.blade.php` | Tabel + modal reject |
| `resources/views/livewire/customer/order-details.blade.php` | Tombol "Bayar via Xendit" |
| `resources/views/livewire/seller/orders.blade.php` | Input ongkir + ship/complete |
| `resources/views/livewire/checkout.blade.php` | Opsi Xendit / COD |

---

## 4. Environment Variables (.env)

```
XENDIT_SECRET_KEY=xnd_development_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
XENDIT_WEBHOOK_TOKEN=your_callback_verification_token_here
XENDIT_BASE_URL=https://api.xendit.co
XENDIT_PLATFORM_FEE_PERCENT=5
```

## 5. Database Schema

### orders (kolom baru)
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| xendit_invoice_id | varchar unique nullable | ID invoice Xendit |
| xendit_invoice_url | text nullable | URL payment page |
| paid_at | timestamp nullable | Waktu pembayaran |

### stores (kolom bank — rename)
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| bank_code | varchar nullable | Kode bank (BCA, BNI, dll) |
| bank_account_no | varchar nullable | Nomor rekening |
| bank_account_name | varchar nullable | Nama pemilik rekening |
| bank_verify_status | enum | unverified, pending, verified, rejected |
| bank_reject_reason | text nullable | Alasan penolakan |

### transactions (tabel baru)
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint PK | |
| order_id | FK orders | |
| seller_id | FK users | |
| total_amount | unsigned int | |
| platform_fee | unsigned int | 5% dari total |
| seller_amount | unsigned int | total - platform_fee |
| xendit_invoice_id | varchar unique | |
| xendit_payment_method | varchar | VA, QRIS, dll |
| xendit_payment_channel | varchar | |
| xendit_invoice_url | text | |
| xendit_disbursement_id | varchar unique | |
| status | enum | pending, paid, expired, disbursed, failed |
| paid_at | timestamp | |
| expired_at | timestamp | |
| disbursed_at | timestamp | |
| metadata | json | |
| timestamps | | |

---

## 6. Route

| Method | URL | Auth | Handler |
|--------|-----|------|---------|
| GET | `/seller/bank` | seller+approved | Livewire `Seller\BankAccount` |
| GET | `/admin/bank-verification` | admin | Livewire `Admin\BankVerification` |
| GET | `/customer/orders/{id}` | customer | Livewire `Customer\OrderDetails` |
| POST | `/api/webhook/xendit` | x-callback-token | `WebhookController@handle` |

---

## 7. Setup

1. `composer require xendit/xendit-php` (sudah)
2. Isi `.env` dengan kredensial Xendit sandbox
3. `php artisan migrate`
4. Setup ngrok: `ngrok http 8000`
5. Daftarkan webhook URL di Xendit Dashboard
6. Test flow end-to-end

---

## 8. Keamanan

- API key via `.env`, tidak di-hardcode
- Webhook divalidasi `x-callback-token` dengan `hash_equals`
- Endpoint webhook di-exclude dari CSRF di `bootstrap/app.php`
- `DB::transaction()` untuk atomic update
- Try-catch di semua panggilan Xendit API