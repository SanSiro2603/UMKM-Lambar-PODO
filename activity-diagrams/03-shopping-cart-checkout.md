# Activity Diagram - Shopping Cart & Checkout

## Proses: Dari Browse Product hingga Checkout Order

```mermaid
flowchart TD
    Start([Customer Browse<br/>Public Storefront]) --> BrowseProducts[Lihat Katalog Produk<br/>Filter, Search, Category]
    
    BrowseProducts --> SelectProduct[Customer Klik<br/>Detail Produk]
    
    SelectProduct --> ViewProductDetail[Lihat Detail:<br/>- Harga<br/>- Deskripsi<br/>- Stok<br/>- Rating<br/>- Foto produk]
    
    ViewProductDetail --> ClickAddToCart[Customer Klik<br/>'Tambah ke Keranjang']
    
    ClickAddToCart --> CheckLoginStatus{Customer<br/>Sudah Login?}
    
    CheckLoginStatus -->|Belum Login| RedirectToLogin[Redirect ke<br/>/login]
    RedirectToLogin --> LoginProcess[Customer Login]
    LoginProcess --> CheckLoginStatus
    
    CheckLoginStatus -->|Sudah Login| CheckStock{Cek Stok<br/>Produk<br/>Tersedia?}
    
    CheckStock -->|Stok Habis| ShowOutOfStock[Tampilkan:<br/>'Produk tidak tersedia']
    ShowOutOfStock --> BrowseProducts
    
    CheckStock -->|Stok Tersedia| CheckCartExist{Produk Sudah<br/>Ada di Cart?}
    
    CheckCartExist -->|Sudah Ada| UpdateQuantity[Update cart_items.qty<br/>qty = qty + 1]
    
    CheckCartExist -->|Belum Ada| InsertToCart[Insert ke cart_items:<br/>user_id, product_id<br/>qty = 1, selected = true]
    
    UpdateQuantity --> ShowSuccessNotif[Notifikasi:<br/>'Berhasil ditambahkan<br/>ke keranjang']
    InsertToCart --> ShowSuccessNotif
    
    ShowSuccessNotif --> ContinueShopping{Customer<br/>Lanjut Belanja?}
    
    ContinueShopping -->|Ya| BrowseProducts
    
    ContinueShopping -->|Tidak| GoToCart[Customer Klik<br/>Icon Keranjang / Menu Cart]
    
    GoToCart --> LoadCartPage[Load /cart<br/>Query cart_items WHERE user_id<br/>JOIN products, stores]
    
    LoadCartPage --> DisplayCartItems[Tampilkan Daftar Item:<br/>- Foto produk<br/>- Nama & harga<br/>- Qty selector<br/>- Checkbox selected<br/>- Grouped by Store]
    
    DisplayCartItems --> CustomerReviewCart{Customer<br/>Review Items}
    
    CustomerReviewCart -->|Update Qty| UpdateQtyInput[Input Qty Baru<br/>Via +/- Button]
    UpdateQtyInput --> ValidateNewQty{Qty Valid?<br/>Stok Cukup?}
    
    ValidateNewQty -->|Qty > Stok| ShowStockError[Error:<br/>'Stok tidak cukup']
    ShowStockError --> CustomerReviewCart
    
    ValidateNewQty -->|Valid| UpdateCartQty[Update cart_items.qty]
    UpdateCartQty --> RecalculateSubtotal[Hitung Ulang Subtotal<br/>qty × price]
    RecalculateSubtotal --> DisplayCartItems
    
    CustomerReviewCart -->|Remove Item| DeleteCartItem[DELETE FROM cart_items<br/>WHERE id = item_id]
    DeleteCartItem --> DisplayCartItems
    
    CustomerReviewCart -->|Toggle Select| ToggleSelected[Update cart_items.selected<br/>= true/false]
    ToggleSelected --> DisplayCartItems
    
    CustomerReviewCart -->|Proceed Checkout| ValidateSelection{Ada Item<br/>yang Selected?}
    
    ValidateSelection -->|Tidak Ada| ShowSelectError[Error:<br/>'Pilih minimal 1 item']
    ShowSelectError --> CustomerReviewCart
    
    ValidateSelection -->|Ada Selected| GroupByStore[Group Selected Items<br/>Berdasarkan Store ID]
    
    GroupByStore --> ShowCheckoutPage[Load /checkout<br/>Tampilkan Form:<br/>- Items grouped by store<br/>- Shipping address form<br/>- Payment method options]
    
    ShowCheckoutPage --> CustomerFillForm[Customer Isi Form:<br/>- Alamat pengiriman<br/>- Pilih metode pembayaran]
    
    CustomerFillForm --> SelectPaymentMethod{Pilih Metode<br/>Pembayaran?}
    
    SelectPaymentMethod -->|Transfer Bank| ShowBankDetails[Tampilkan Info Bank<br/>dari store_payment_methods:<br/>- Nama bank<br/>- No rekening<br/>- Atas nama]
    
    SelectPaymentMethod -->|E-wallet - DANA| ShowDANADetails[Tampilkan:<br/>- Nomor DANA<br/>- Atas nama<br/>- QR Code (if any)]
    
    SelectPaymentMethod -->|COD| ShowCODInfo[Tampilkan Info:<br/>'Bayar saat barang tiba']
    
    ShowBankDetails --> CustomerConfirmOrder
    ShowDANADetails --> CustomerConfirmOrder
    ShowCODInfo --> CustomerConfirmOrder[Customer Review<br/>Total & Confirm Order]
    
    CustomerConfirmOrder --> ClickPlaceOrder[Customer Klik<br/>'Buat Pesanan']
    
    ClickPlaceOrder --> ValidateCheckoutForm{Validasi Form<br/>Alamat Terisi?}
    
    ValidateCheckoutForm -->|Tidak Valid| ShowFormError[Error:<br/>'Alamat harus diisi']
    ShowFormError --> CustomerFillForm
    
    ValidateCheckoutForm -->|Valid| BeginTransaction[BEGIN DATABASE<br/>TRANSACTION]
    
    BeginTransaction --> CreateOrders[LOOP: Per Store<br/>Create Order Record:<br/>- Generate order_code<br/>- customer_id<br/>- store_id<br/>- total_price<br/>- shipping_address<br/>- payment_method<br/>- status = 'pending']
    
    CreateOrders --> CreateOrderItems[LOOP: Per Selected Item<br/>INSERT INTO order_items:<br/>- order_id<br/>- product_id<br/>- qty<br/>- price (snapshot)]
    
    CreateOrderItems --> UpdateProductStock[UPDATE products<br/>SET stock = stock - qty<br/>WHERE product_id IN (...)]
    
    UpdateProductStock --> DeleteFromCart[DELETE FROM cart_items<br/>WHERE id IN (selected items)]
    
    DeleteFromCart --> CommitTransaction[COMMIT TRANSACTION]
    
    CommitTransaction --> TriggerNotification[Laravel Event:<br/>OrderCreated<br/>→ Notify Seller via Echo]
    
    TriggerNotification --> CheckPaymentType{Payment Method?}
    
    CheckPaymentType -->|Transfer/E-wallet| RedirectToUploadProof[Redirect ke<br/>Order Detail Page<br/>dengan Upload Form]
    
    CheckPaymentType -->|COD| RedirectToOrderList[Redirect ke<br/>/customer/orders<br/>Status: Menunggu Konfirmasi]
    
    RedirectToUploadProof --> ShowUploadForm[Tampilkan Form<br/>Upload Bukti Transfer]
    
    ShowUploadForm --> CustomerUploadProof[Customer Upload<br/>Foto Bukti Pembayaran]
    
    CustomerUploadProof --> SaveProofImage[Save Image:<br/>UPDATE orders<br/>SET proof_of_transfer<br/>status = 'menunggu_validasi']
    
    SaveProofImage --> NotifySellerNewProof[Notifikasi Real-time<br/>ke Seller:<br/>'Bukti transfer diterima']
    
    NotifySellerNewProof --> OrderWaitingValidation[Order Status:<br/>'Menunggu Validasi Penjual']
    
    RedirectToOrderList --> OrderWaitingConfirmation[Order Status:<br/>'Menunggu Konfirmasi' COD]
    
    OrderWaitingValidation --> End([Checkout Complete<br/>Menunggu Seller Validasi])
    OrderWaitingConfirmation --> End
    
    style Start fill:#e1f5ff
    style End fill:#c8e6c9
    style ShowOutOfStock fill:#ffccbc
    style ShowStockError fill:#ffccbc
    style ShowSelectError fill:#ffccbc
    style ShowFormError fill:#ffccbc
    style BeginTransaction fill:#b2dfdb
    style CommitTransaction fill:#b2dfdb
    style CreateOrders fill:#fff9c4
    style CreateOrderItems fill:#fff9c4
    style TriggerNotification fill:#b2dfdb
```

## Penjelasan Detail Alur:

### **FASE 1: Browse & Add to Cart**

#### 1. **Product Discovery**
Customer dapat menemukan produk melalui:
- Homepage featured products
- Browse by category
- Search bar (keyword search)
- Visit store profile page
- Recommended products

#### 2. **Add to Cart Logic**
```php
// Check if user logged in
if (!Auth::check()) {
    return redirect()->route('login')
        ->with('intended', current_url);
}

// Check stock availability
if ($product->stock < 1) {
    return error('Produk tidak tersedia');
}

// Check if product already in cart
$cartItem = CartItem::where('user_id', Auth::id())
                    ->where('product_id', $productId)
                    ->first();

if ($cartItem) {
    // Update quantity
    $cartItem->increment('qty');
} else {
    // Insert new cart item
    CartItem::create([
        'user_id' => Auth::id(),
        'product_id' => $productId,
        'qty' => 1,
        'selected' => true
    ]);
}
```

#### 3. **Cart Constraints**
- Unique constraint: `(user_id, product_id)` → Prevent duplicates
- `selected` default: `true` → Auto-selected for checkout
- Real-time price calculation

---

### **FASE 2: Cart Management**

#### **Cart Page Display**
```
Route: /cart
Component: CartPage (Livewire)

Query:
SELECT 
    ci.*, 
    p.name, p.price, p.image, p.stock,
    s.name as store_name, s.slug as store_slug
FROM cart_items ci
JOIN products p ON ci.product_id = p.id
JOIN stores s ON p.store_id = s.id
WHERE ci.user_id = {auth_user_id}
ORDER BY s.name, p.name
```

**Grouping Logic:**
```php
$cartItems->groupBy('store_id')->map(function($items, $storeId) {
    return [
        'store' => Store::find($storeId),
        'items' => $items,
        'subtotal' => $items->sum(fn($item) => $item->qty * $item->product->price)
    ];
});
```

#### **Cart Actions:**

**1. Update Quantity:**
```php
// Validate against stock
if ($newQty > $product->stock) {
    return error('Stok tidak cukup. Tersedia: ' . $product->stock);
}

if ($newQty < 1) {
    return error('Quantity minimal 1');
}

$cartItem->update(['qty' => $newQty]);
```

**2. Remove Item:**
```php
CartItem::where('id', $cartItemId)
         ->where('user_id', Auth::id())
         ->delete();
```

**3. Toggle Selection:**
```php
$cartItem->update([
    'selected' => !$cartItem->selected
]);
```

**4. Subtotal Calculation:**
```php
// Per item
$itemSubtotal = $cartItem->qty * $cartItem->product->price;

// Per store
$storeSubtotal = $storeItems->sum('itemSubtotal');

// Total (all stores)
$grandTotal = $allItems->where('selected', true)
                       ->sum('itemSubtotal');
```

---

### **FASE 3: Checkout Process**

#### **Pre-Checkout Validation**
```php
// Check if any item selected
$selectedItems = $cartItems->where('selected', true);

if ($selectedItems->isEmpty()) {
    return error('Pilih minimal 1 item untuk checkout');
}

// Check stock availability for all selected items
foreach ($selectedItems as $item) {
    if ($item->qty > $item->product->stock) {
        return error("Stok {$item->product->name} tidak cukup");
    }
}
```

#### **Multi-Store Order Splitting**
```php
// Group by store
$ordersByStore = $selectedItems->groupBy('product.store_id');

// Each store = 1 separate order
foreach ($ordersByStore as $storeId => $items) {
    // Create individual order for this store
}
```

**Example:**
```
Cart Contents:
  Store A: Product 1, Product 2
  Store B: Product 3
  Store C: Product 4, Product 5

Result after Checkout:
  Order #1 → Store A (2 items)
  Order #2 → Store B (1 item)
  Order #3 → Store C (2 items)

Total: 3 Orders Created
```

---

### **FASE 4: Order Creation (Database Transaction)**

#### **Transaction Flow:**
```php
DB::beginTransaction();
try {
    foreach ($ordersByStore as $storeId => $items) {
        // 1. Create Order
        $order = Order::create([
            'order_code' => generateOrderCode(), // e.g., ORD-20260608-001
            'customer_id' => Auth::id(),
            'store_id' => $storeId,
            'total_price' => $items->sum('subtotal'),
            'shipping_cost' => 0, // or calculated
            'shipping_address' => $request->address,
            'payment_method' => $request->payment_method, // 'transfer' or 'cod'
            'payment_status' => 'unpaid',
            'status' => 'pending'
        ]);
        
        // 2. Create Order Items
        foreach ($items as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'qty' => $cartItem->qty,
                'price' => $cartItem->product->price // Price snapshot
            ]);
            
            // 3. Update Product Stock
            Product::where('id', $cartItem->product_id)
                   ->decrement('stock', $cartItem->qty);
        }
        
        // 4. Delete from Cart
        CartItem::whereIn('id', $items->pluck('id'))->delete();
        
        // 5. Trigger Real-time Notification
        event(new OrderCreated($order));
    }
    
    DB::commit();
    
} catch (\Exception $e) {
    DB::rollBack();
    return error('Checkout gagal: ' . $e->getMessage());
}
```

---

### **FASE 5: Payment Method Handling**

#### **A. Transfer Bank / E-wallet**

**Display Store Payment Info:**
```php
$paymentMethods = StorePaymentMethod::where('store_id', $order->store_id)
                                    ->get();

// Example output:
[
    {
        type: 'bank',
        name: 'Bank Mandiri',
        account_name: 'Toko Sejahtera',
        account_number: '1234567890'
    },
    {
        type: 'dana',
        name: 'DANA',
        account_name: 'Toko Sejahtera',
        account_number: '081234567890',
        qr_code: '/storage/qr/dana-12345.png'
    }
]
```

**Upload Proof Flow:**
```php
// Customer uploads proof
$proofPath = $request->file('proof')->store('proofs', 'public');

$order->update([
    'proof_of_transfer' => $proofPath,
    'payment_status' => 'paid',
    'status' => 'menunggu_validasi'
]);

// Notify seller via Laravel Echo
event(new OrderPaymentUploaded($order));
```

#### **B. COD (Cash on Delivery)**

```php
$order->update([
    'payment_method' => 'cod',
    'payment_status' => 'unpaid',
    'status' => 'pending' // Waiting for seller confirmation
]);

// Seller will approve COD order manually
// Payment validated after delivery
```

---

### **FASE 6: Post-Checkout**

#### **Order Status Flow:**

**Transfer/E-wallet:**
```
pending → (customer upload proof) → menunggu_validasi 
    → (seller approve) → diproses → dikirim → selesai
```

**COD:**
```
pending → (seller approve) → diproses → dikirim 
    → (customer confirm received + paid) → selesai
```

#### **Real-time Notifications:**
```javascript
// Seller Dashboard - Listen for new orders
Echo.private(`stores.${storeId}`)
    .listen('OrderCreated', (event) => {
        // Play sound notification
        playNotificationSound();
        
        // Update order count badge
        updateOrderBadge();
        
        // Show toast notification
        showToast('Pesanan baru masuk!');
    });
```

---

## Database State Changes:

### **Add to Cart:**
```sql
INSERT INTO cart_items (user_id, product_id, qty, selected)
VALUES (1, 100, 1, true)
ON DUPLICATE KEY UPDATE qty = qty + 1;
```

### **Checkout - Multi Insert:**
```sql
-- Per store order
INSERT INTO orders (order_code, customer_id, store_id, total_price, ...)
VALUES ('ORD-20260608-001', 1, 5, 150000, ...);

-- Per product in order
INSERT INTO order_items (order_id, product_id, qty, price)
VALUES 
    (1001, 100, 2, 50000),
    (1001, 101, 1, 50000);

-- Update stock
UPDATE products SET stock = stock - 2 WHERE id = 100;
UPDATE products SET stock = stock - 1 WHERE id = 101;

-- Clear cart
DELETE FROM cart_items WHERE id IN (10, 11);
```

---

## Business Rules & Edge Cases:

### 🛒 **Cart Rules:**
1. ✅ Guest cannot add to cart (must login first)
2. ✅ Cannot add out-of-stock products
3. ✅ Cannot exceed available stock
4. ✅ Cart persists across sessions (database-backed)
5. ✅ Auto-remove items if product deleted by seller

### 💳 **Checkout Rules:**
1. ✅ Minimum 1 selected item
2. ✅ Stock validation before order creation
3. ✅ Multi-store orders split automatically
4. ✅ Price snapshot at checkout time (in order_items)
5. ✅ Transaction rollback if any step fails

### 🔔 **Notification Rules:**
1. ✅ Real-time notification to seller (Laravel Echo)
2. ✅ Sound alert on seller dashboard
3. ✅ Email notification (optional, queued)
4. ✅ Order count badge update

### ⚠️ **Error Handling:**
- Out of stock → Error + suggest similar products
- Payment upload fail → Can re-upload anytime
- Transaction fail → Full rollback + error message
- Concurrent stock update → Pessimistic locking
