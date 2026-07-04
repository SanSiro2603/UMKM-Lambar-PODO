# Activity Diagram - Login dan Autentikasi

## Proses: Login dan Autentikasi Multi-Role

```mermaid
flowchart TD
    Start([User Mengakses Halaman Login]) --> InputCredentials[User Input Email & Password]
    
    InputCredentials --> SubmitForm[User Klik Login]
    
    SubmitForm --> ValidateInput{Validasi Input<br/>Email & Password Valid?}
    
    ValidateInput -->|Tidak Valid| ShowValidationError[Tampilkan Error Validasi<br/>Email/Password Required]
    ShowValidationError --> InputCredentials
    
    ValidateInput -->|Valid| CheckDatabase{Cek Database<br/>Email Terdaftar?}
    
    CheckDatabase -->|Email Tidak Ditemukan| ShowEmailError[Tampilkan Error:<br/>Email tidak terdaftar]
    ShowEmailError --> InputCredentials
    
    CheckDatabase -->|Email Ditemukan| VerifyPassword{Verifikasi Password<br/>Hash Match?}
    
    VerifyPassword -->|Password Salah| ShowPasswordError[Tampilkan Error:<br/>Password salah]
    ShowPasswordError --> InputCredentials
    
    VerifyPassword -->|Password Benar| CreateSession[Buat Session Laravel<br/>Store User ID & Role]
    
    CreateSession --> CheckRole{Cek Role User?}
    
    CheckRole -->|Role: Admin| RedirectAdmin[Redirect ke<br/>/admin/dashboard]
    
    CheckRole -->|Role: Seller| CheckSellerStatus{Cek Status Toko<br/>Approved?}
    
    CheckSellerStatus -->|Status: Pending| ShowPendingMessage[Tampilkan Message:<br/>Toko menunggu approval]
    ShowPendingMessage --> RedirectSellerDashboard[Redirect ke<br/>/seller/dashboard]
    
    CheckSellerStatus -->|Status: Rejected| ShowRejectedMessage[Tampilkan Message:<br/>Toko ditolak admin]
    ShowRejectedMessage --> RedirectSellerDashboard
    
    CheckSellerStatus -->|Status: Approved| RedirectSellerDashboard
    
    CheckRole -->|Role: Customer| RedirectCustomer[Redirect ke<br/>Homepage atau Previous URL]
    
    RedirectAdmin --> LoadDashboard[Load Dashboard dengan<br/>Data sesuai Role]
    RedirectSellerDashboard --> LoadDashboard
    RedirectCustomer --> LoadDashboard
    
    LoadDashboard --> End([Login Berhasil])
    
    style Start fill:#e1f5ff
    style End fill:#c8e6c9
    style ShowValidationError fill:#ffccbc
    style ShowEmailError fill:#ffccbc
    style ShowPasswordError fill:#ffccbc
    style ShowPendingMessage fill:#fff9c4
    style ShowRejectedMessage fill:#ffccbc
    style CreateSession fill:#b2dfdb
    style LoadDashboard fill:#b2dfdb
```

## Penjelasan Alur:

### 1. **Input & Validasi Awal**
- User mengakses halaman `/login`
- Input email dan password
- Client-side validation (required fields)

### 2. **Verifikasi Database**
- Cek apakah email terdaftar di tabel `users`
- Jika tidak ditemukan → Error "Email tidak terdaftar"

### 3. **Verifikasi Password**
- Hash password input dibandingkan dengan hash di database
- Menggunakan `bcrypt` verification
- Jika tidak match → Error "Password salah"

### 4. **Session Creation**
- Laravel membuat session dengan `Auth::attempt()`
- Store user ID, role, dan data lainnya dalam session
- Generate `remember_token` jika "Remember Me" dicentang

### 5. **Role-Based Redirect**

#### **Admin:**
- Direct redirect ke `/admin/dashboard`
- Akses penuh ke semua fitur admin

#### **Seller:**
- Cek status toko di tabel `stores`
- **Status Pending**: Redirect ke dashboard dengan notifikasi menunggu approval
- **Status Rejected**: Redirect dengan notifikasi penolakan
- **Status Approved**: Redirect ke dashboard dengan akses penuh

#### **Customer:**
- Redirect ke homepage atau URL sebelum login (intended URL)
- Session keranjang digabungkan jika ada

### 6. **Completion**
- Dashboard/Homepage dimuat dengan data sesuai role
- Navbar menampilkan menu sesuai hak akses

---

## Security Features:
- ✅ Password hashing dengan bcrypt
- ✅ CSRF token protection
- ✅ Rate limiting (mencegah brute force)
- ✅ Session timeout management
- ✅ Remember token untuk "Stay logged in"

---

## Error Handling:
1. **Validation Errors**: Input kosong atau format salah
2. **Authentication Errors**: Email tidak ditemukan atau password salah
3. **Authorization Errors**: Toko seller belum di-approve
