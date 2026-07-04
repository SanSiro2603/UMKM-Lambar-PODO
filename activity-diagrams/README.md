# Activity Diagrams - UMKM Air Hitam Platform

Dokumentasi lengkap alur logika bisnis sistem UMKM Air Hitam menggunakan Activity Diagram (Mermaid.js).

## 📋 Daftar Activity Diagrams

### 1. [Login dan Autentikasi](./01-login-authentication.md)
**Proses:** Multi-role authentication dengan role-based redirect
- Input credentials & validation
- Database verification
- Password hash checking
- Role detection (Admin/Seller/Customer)
- Session creation
- Seller store status validation
- Conditional redirect based on role

**Key Features:**
- ✅ Multi-role support (Admin, Seller, Customer)
- ✅ Store approval check untuk Seller
- ✅ CSRF protection
- ✅ Rate limiting
- ✅ Remember me functionality

---

### 2. [Registrasi dan Approval Seller](./02-seller-registration-approval.md)
**Proses:** Seller onboarding workflow dengan admin approval
- Form registrasi seller
- Upload dokumen (KTP, Logo, Banner)
- Email uniqueness check
- Store record creation dengan status "pending"
- Admin notification
- Admin review process
- Approval/Rejection workflow
- Email notification ke seller

**Key Features:**
- ✅ Two-phase process (Registration → Approval)
- ✅ Document upload & compression
- ✅ Status: pending → approved/rejected
- ✅ Store activation setelah approval
- ✅ Real-time admin notification

---

### 3. [Shopping Cart & Checkout](./03-shopping-cart-checkout.md)
**Proses:** Dari browse product hingga checkout dengan multi-store order splitting
- Product browsing & discovery
- Add to cart (login required)
- Stock availability check
- Cart management (update qty, remove item, toggle selection)
- Checkout validation
- Multi-store order splitting
- Payment method selection
- Order creation (database transaction)
- Upload bukti transfer

**Key Features:**
- ✅ Guest can browse, must login to buy
- ✅ Unique constraint (user_id, product_id) di cart
- ✅ Multi-store cart grouping
- ✅ Automatic order splitting per store
- ✅ Real-time notification ke seller
- ✅ Transaction rollback on failure

---

### 4. [Payment Validation oleh Seller](./04-order-payment-validation-seller.md)
**Proses:** Manual validation bukti transfer oleh seller
- Real-time notification ke seller (Laravel Echo)
- Seller view order detail
- Seller check bank mutation (manual)
- Comparison: bukti vs mutasi rekening
- Approve/Reject decision
- Update order status
- Customer notification
- COD order handling

**Key Features:**
- ✅ Real-time WebSocket notification
- ✅ Sound alert untuk seller
- ✅ Manual validation (seller cek rekening sendiri)
- ✅ Rejection dengan alasan
- ✅ Customer can re-upload if rejected
- ✅ Separate flow untuk COD orders

---

### 5. [Product Management Seller](./05-product-management-seller.md)
**Proses:** CRUD produk oleh seller
- Store approval check
- Product list display
- Create product (form + image upload)
- Image compression
- Slug generation
- Edit product
- Soft delete vs hard delete
- Bulk stock update
- Delete cascade handling

**Key Features:**
- ✅ Only approved stores can manage products
- ✅ Automatic image compression (max 1200px, quality 80%)
- ✅ Unique slug generation
- ✅ Soft delete untuk produk dengan order aktif
- ✅ Bulk actions support
- ✅ Foreign key cascade handling

---

### 6. [Order Status Lifecycle](./06-order-status-lifecycle.md)
**Proses:** Complete order flow dari pending hingga selesai
- Order creation
- Payment method split (Transfer vs COD)
- Payment validation workflow
- Processing stage
- Shipping update (tracking number)
- Delivery confirmation
- Auto-complete setelah 7 hari
- Product sold count update
- Seller statistics update

**Key Features:**
- ✅ 6 status states: pending, menunggu_validasi, diproses, dikirim, selesai, dibatalkan
- ✅ Different workflow untuk Transfer vs COD
- ✅ Status transition rules
- ✅ Event-driven architecture
- ✅ Auto-complete untuk order lama
- ✅ Review request setelah completion

---

## 🎯 Key Concepts

### **Multi-Role System**
- **Guest**: Browse only
- **Customer**: Browse + Transact
- **Seller**: Manage store + products + orders
- **Admin**: Platform governance

### **Order Status States**
```
pending → menunggu_validasi → diproses → dikirim → selesai
                ↓                ↓
            dibatalkan      dibatalkan
```

### **Payment Methods**
1. **Transfer Bank/E-wallet**: Requires proof upload + seller validation
2. **COD**: Direct approval by seller, payment after delivery

### **Real-time Features**
- Laravel Echo + Reverb (WebSocket)
- Instant notification ke seller saat order baru
- Sound alert
- Badge count update

### **Database Transactions**
- Checkout: Multi-insert (orders + order_items) + stock update + cart clear
- All-or-nothing dengan rollback on failure

### **Image Handling**
- Auto-compression (max 1200px, quality 80%)
- ImageCompressor helper class
- Storage: storage/app/public/

---

## 📊 Business Rules Summary

### **Cart Rules**
- ❌ Guest cannot add to cart
- ✅ Must login first
- ✅ Stock validation
- ✅ Unique constraint per user+product
- ✅ Cart persists across sessions

### **Checkout Rules**
- ✅ Minimum 1 selected item
- ✅ Stock validation before order creation
- ✅ Multi-store orders split automatically
- ✅ Price snapshot in order_items
- ✅ Transaction rollback on failure

### **Payment Rules**
- ✅ Transfer: Manual validation by seller
- ✅ COD: Seller approval before processing
- ✅ Rejection allows re-upload
- ✅ Stock restore on cancellation (optional)

### **Product Rules**
- ✅ Only approved stores can add products
- ✅ Slug must be unique globally
- ✅ Price min: Rp 100
- ✅ Stock cannot be negative
- ✅ Soft delete recommended if has active orders

---

## 🔒 Security Features

### **Authentication**
- ✅ Bcrypt password hashing
- ✅ CSRF token protection
- ✅ Rate limiting (prevent brute force)
- ✅ Session timeout management

### **Authorization**
- ✅ Role-based middleware
- ✅ Store ownership validation
- ✅ Order ownership validation
- ✅ File upload validation (type, size)

### **Data Integrity**
- ✅ Foreign key constraints
- ✅ Database transactions
- ✅ Pessimistic locking (prevent race conditions)
- ✅ Cascade delete protection

---

## 🚀 Performance Optimization

### **Database**
- ✅ Eager loading relationships
- ✅ Pagination (15 items/page)
- ✅ Index on foreign keys
- ✅ Caching for statistics

### **Images**
- ✅ Auto-compression
- ✅ Lazy loading on frontend
- ✅ CDN-ready (storage/public)

### **Real-time**
- ✅ WebSocket (efficient vs polling)
- ✅ Event broadcasting
- ✅ Queue for background jobs

---

## 📈 Metrics & Analytics

### **Order Funnel**
```
Total Orders
├─ Pending: X%
├─ Validated: Y%
├─ Processing: Z%
├─ Shipped: A%
├─ Completed: B%
└─ Cancelled: C%
```

### **Success Rate**
```
Success Rate = (Completed Orders / Total Orders) × 100%
```

### **Average Completion Time**
```
Avg Time = AVG(completed_at - created_at)
```

---

## 🛠️ Technology Stack

- **Framework**: Laravel 12
- **Frontend**: Livewire 3 + Alpine.js
- **Styling**: Tailwind CSS
- **Database**: MySQL 8.0+
- **Cache/Queue**: Redis
- **Real-time**: Laravel Reverb (WebSocket)
- **PDF**: spatie/laravel-pdf
- **Media**: spatie/laravel-medialibrary
- **Permissions**: spatie/laravel-permission

---

## 📝 Diagram Legend

### **Node Colors**
- 🔵 **Blue** (e1f5ff): Start/Entry point
- 🟢 **Green** (c8e6c9): Success/Completion
- 🔴 **Red** (ffccbc): Error/Rejection
- 🟡 **Yellow** (fff9c4): Waiting/Pending state
- 🟢 **Teal** (b2dfdb): Database operation

### **Flow Types**
- **Solid Arrow** (→): Main flow
- **Dashed Arrow** (-.->): Optional/Alternative flow
- **Diamond** (◇): Decision point
- **Rectangle** (▭): Process/Action
- **Rounded** (▢): Terminal state

---

## 📚 How to Use These Diagrams

### **For Developers:**
1. Understand business logic flow
2. Implement features based on diagrams
3. Reference for database operations
4. Debug workflow issues

### **For QA/Testers:**
1. Create test scenarios
2. Validate all paths (success + failure)
3. Edge case identification
4. User acceptance testing

### **For Product Managers:**
1. Visualize user journeys
2. Identify bottlenecks
3. Plan feature enhancements
4. Business rule documentation

### **For Stakeholders:**
1. Understand system capabilities
2. Workflow transparency
3. Decision approval points
4. Process optimization opportunities

---

## 🔄 Workflow Relationships

```
01. Login → 02. Seller Registration (if seller role)
             → 03. Shopping Cart (if customer role)

02. Seller Registration → 05. Product Management (after approval)

03. Shopping Cart → 04. Payment Validation → 06. Order Lifecycle

05. Product Management → influences → 03. Shopping Cart

06. Order Lifecycle → updates → 05. Product (sold count)
                   → triggers → 04. Payment Validation
```

---

## 📞 Support & Documentation

Untuk pertanyaan atau klarifikasi tentang diagram ini:
- Lihat kode implementasi di repository
- Check dokumentasi Laravel Livewire
- Konsultasi dengan lead developer
- Review PRD dokumen: `prd_umkm_air_hitam_.md`

---

**Last Updated**: June 8, 2026
**Version**: 1.0
**Author**: System Analyst Team
