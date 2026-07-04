# Activity Diagram - Registrasi dan Approval Seller

## Proses: Seller Onboarding Workflow

```mermaid
flowchart TD
    Start([Calon Seller Akses<br/>Halaman Registrasi Seller]) --> FillForm[Isi Form Registrasi:<br/>- Nama Lengkap<br/>- Email<br/>- Password<br/>- Nomor Telepon<br/>- Alamat]
    
    FillForm --> FillStoreInfo[Isi Informasi Toko:<br/>- Nama Toko<br/>- Deskripsi Toko<br/>- Alamat Toko<br/>- Info Bank/E-wallet]
    
    FillStoreInfo --> UploadDocuments[Upload Dokumen:<br/>- Foto KTP<br/>- Logo Toko (optional)<br/>- Banner Toko (optional)]
    
    UploadDocuments --> SubmitRegistration[Submit Registrasi]
    
    SubmitRegistration --> ValidateForm{Validasi Form<br/>Semua Field Required<br/>Terisi?}
    
    ValidateForm -->|Ada yang Kosong| ShowValidationError[Tampilkan Error<br/>Field yang harus diisi]
    ShowValidationError --> FillForm
    
    ValidateForm -->|Valid| CheckEmailUnique{Email Sudah<br/>Terdaftar?}
    
    CheckEmailUnique -->|Sudah Ada| ShowEmailExistError[Error: Email sudah digunakan]
    ShowEmailExistError --> FillForm
    
    CheckEmailUnique -->|Unique| CreateUserRecord[Buat Record di Tabel users:<br/>- role = 'seller'<br/>- Hash password<br/>- Store user data]
    
    CreateUserRecord --> CreateStoreRecord[Buat Record di Tabel stores:<br/>- user_id = user.id<br/>- status = 'pending'<br/>- Generate slug dari nama toko<br/>- Store bank info]
    
    CreateStoreRecord --> CompressUploadedFiles[Compress & Upload Files:<br/>- KTP Photo<br/>- Logo (if any)<br/>- Banner (if any)]
    
    CompressUploadedFiles --> SendNotificationToAdmin[Kirim Notifikasi ke Admin:<br/>Email/System Notification<br/>Ada Seller Baru Pending]
    
    SendNotificationToAdmin --> ShowSuccessMessage[Tampilkan Pesan Sukses:<br/>'Pendaftaran berhasil,<br/>menunggu persetujuan admin']
    
    ShowSuccessMessage --> SellerWaiting[Status: Waiting for Approval<br/>Seller dapat login tapi<br/>toko belum tampil di public]
    
    %% Admin Review Process
    SellerWaiting -.->|Admin Action| AdminLogin[Admin Login ke Dashboard]
    
    AdminLogin --> AdminViewSellerList[Admin Buka Menu<br/>/admin/sellers<br/>Lihat Daftar Seller Pending]
    
    AdminViewSellerList --> AdminSelectSeller[Admin Klik Detail<br/>Seller yang akan direview]
    
    AdminSelectSeller --> AdminReviewDocuments[Admin Review:<br/>- Data pribadi seller<br/>- Info toko<br/>- Foto KTP<br/>- Info bank/rekening]
    
    AdminReviewDocuments --> AdminDecision{Keputusan Admin?}
    
    AdminDecision -->|Approve| UpdateStatusApproved[Update stores.status<br/>= 'approved']
    
    UpdateStatusApproved --> ActivateStore[Toko Aktif:<br/>- Tampil di Public Storefront<br/>- Seller dapat manage produk<br/>- Toko bisa menerima order]
    
    ActivateStore --> SendApprovalEmail[Kirim Email ke Seller:<br/>'Toko Anda telah disetujui']
    
    SendApprovalEmail --> SellerCanSell[Seller Mulai Berjualan]
    
    AdminDecision -->|Reject| UpdateStatusRejected[Update stores.status<br/>= 'rejected']
    
    UpdateStatusRejected --> SendRejectionEmail[Kirim Email ke Seller:<br/>'Pendaftaran ditolak'<br/>+ Alasan penolakan]
    
    SendRejectionEmail --> SellerCannotSell[Seller Tidak Dapat Berjualan<br/>Toko tidak muncul di public]
    
    SellerCanSell --> End([Seller Onboarding Complete])
    SellerCannotSell --> End
    
    style Start fill:#e1f5ff
    style End fill:#c8e6c9
    style ShowValidationError fill:#ffccbc
    style ShowEmailExistError fill:#ffccbc
    style CreateUserRecord fill:#b2dfdb
    style CreateStoreRecord fill:#b2dfdb
    style SellerWaiting fill:#fff9c4
    style UpdateStatusApproved fill:#c8e6c9
    style ActivateStore fill:#c8e6c9
    style UpdateStatusRejected fill:#ffccbc
    style SellerCannotSell fill:#ffccbc
```

## Penjelasan Detail Alur:

### **FASE 1: Registrasi Seller (Self-Service)**

#### 1. **Form Input**
- **Data Personal**:
  - Nama lengkap
  - Email (unique)
  - Password (min 8 karakter)
  - Nomor telepon
  - Alamat lengkap

- **Data Toko**:
  - Nama toko
  - Slug (auto-generated dari nama toko)
  - Deskripsi toko
  - Alamat operasional toko

- **Informasi Pembayaran**:
  - Bank name (e.g., "Bank Mandiri")
  - Nomor rekening
  - Nama pemilik rekening
  - Atau: E-wallet (DANA/GoPay/OVO)

#### 2. **Upload Dokumen**
- **KTP Photo** (Required): Untuk verifikasi identitas
- **Logo Toko** (Optional): Branding
- **Banner Toko** (Optional): Header toko

#### 3. **Validasi & Processing**
```php
// Validasi
- Email unique check → Query ke users table
- Phone format validation
- Password strength check
- File type validation (jpg, png, max 2MB)

// Database Transaction
BEGIN TRANSACTION;
  INSERT INTO users (name, email, password, role='seller', phone, address);
  INSERT INTO stores (user_id, name, slug, status='pending', bank_info);
  Upload files → Storage (spatie/laravel-medialibrary);
  Compress images → ImageCompressor helper;
COMMIT;
```

#### 4. **Status Awal**
- User record: `role = 'seller'`
- Store record: `status = 'pending'`
- Toko **TIDAK** muncul di Public Storefront
- Seller **BISA** login tapi tidak bisa manage produk

---

### **FASE 2: Admin Review & Approval**

#### 1. **Admin Notification**
Ketika seller baru registrasi:
- Notifikasi muncul di admin dashboard
- Badge counter "Pending Sellers" bertambah
- Email notification ke admin (optional)

#### 2. **Admin Review Process**
Admin membuka `/admin/sellers` dan melihat:
- List seller dengan status `pending`
- Dapat klik "Detail" untuk review

**Informasi yang di-review:**
```
✓ Data pribadi seller (nama, email, phone)
✓ Data toko (nama, deskripsi, alamat)
✓ Foto KTP (untuk verifikasi identitas)
✓ Info rekening bank/e-wallet
✓ Kelengkapan dokumen
```

#### 3. **Admin Decision**

**Option A: APPROVE**
```sql
UPDATE stores 
SET status = 'approved' 
WHERE id = {store_id};
```
**Hasil:**
- ✅ Toko muncul di Public Storefront
- ✅ Seller bisa menambah produk
- ✅ Seller bisa menerima order
- ✅ Dashboard seller full-access
- 📧 Email notifikasi: "Toko Anda telah disetujui"

**Option B: REJECT**
```sql
UPDATE stores 
SET status = 'rejected' 
WHERE id = {store_id};
```
**Hasil:**
- ❌ Toko tetap tidak muncul di public
- ❌ Seller tidak bisa manage produk
- ❌ Seller tidak bisa terima order
- 📧 Email notifikasi: "Pendaftaran ditolak" + alasan

---

### **FASE 3: Post-Approval**

#### **Jika Approved:**
Seller mendapat akses penuh:
1. ✅ Manage Products (CRUD)
2. ✅ Manage Store Profile
3. ✅ View & Process Orders
4. ✅ View Sales Reports
5. ✅ Toko tampil di katalog public

#### **Jika Rejected:**
Seller options:
- Bisa registrasi ulang dengan data yang benar
- Atau contact admin untuk klarifikasi

---

## Database State Changes:

### **Registration Flow:**
```
users table:
  INSERT → { role: 'seller', ... }

stores table:
  INSERT → { user_id: X, status: 'pending', ... }

Livewire Event:
  dispatch('seller-registered', sellerId)
```

### **Approval Flow:**
```
stores table:
  UPDATE → { status: 'approved' }

Laravel Event:
  event(new SellerApproved($store))
  
Mail Queue:
  SendSellerApprovalEmail($seller)
```

### **Rejection Flow:**
```
stores table:
  UPDATE → { status: 'rejected' }

Laravel Event:
  event(new SellerRejected($store))
  
Mail Queue:
  SendSellerRejectionEmail($seller, $reason)
```

---

## Security & Business Rules:

### 🔒 **Security:**
- Email verification before approval (optional)
- KTP validation by admin
- Bank account verification
- Rate limiting on registration endpoint

### 📋 **Business Rules:**
1. 1 Email = 1 User = 1 Store (One-to-One)
2. Store slug harus unique
3. Seller tidak bisa ubah data bank tanpa admin approval
4. Rejected seller bisa registrasi ulang dengan email berbeda

### ⏱️ **Workflow Timing:**
- Registration: Instant (< 5 seconds)
- Admin Review: Manual (depends on admin availability)
- Approval Process: < 1 second
- Email Notification: Queued (background job)
