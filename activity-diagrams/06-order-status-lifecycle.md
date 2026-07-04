# Activity Diagram - Order Status Lifecycle

## Proses: Complete Order Status Flow dari Pending hingga Selesai

```mermaid
flowchart TD
    Start([Customer Checkout<br/>Order Created]) --> InitialStatus[Order Status:<br/>'pending'<br/>Payment Status: 'unpaid']
    
    InitialStatus --> CheckPaymentMethod{Payment<br/>Method?}
    
    %% TRANSFER FLOW
    CheckPaymentMethod -->|Transfer/E-wallet| WaitingProof[Status: 'pending'<br/>Payment: 'unpaid'<br/>Waiting for customer<br/>upload proof]
    
    WaitingProof --> CustomerUpload{Customer<br/>Upload Proof?}
    
    CustomerUpload -->|Not Uploaded Yet| WaitingProof
    
    CustomerUpload -->|Uploaded| UpdateToWaitingValidation[Status: 'menunggu_validasi'<br/>Payment: 'paid'<br/>Proof uploaded,<br/>waiting seller validation]
    
    UpdateToWaitingValidation --> NotifySellerProof[Notification:<br/>Real-time ke Seller<br/>'Bukti transfer diterima']
    
    NotifySellerProof --> SellerValidation{Seller<br/>Validasi Bukti?}
    
    SellerValidation -->|Approve| UpdateToProcessing[Status: 'diproses'<br/>Payment: 'verified'<br/>Seller preparing order]
    
    SellerValidation -->|Reject| RejectPayment[Status: 'pending'<br/>Payment: 'failed'<br/>Rejection reason stored]
    
    RejectPayment --> NotifyCustomerRejection[Notification Customer:<br/>'Pembayaran ditolak'<br/>+ Alasan<br/>+ Re-upload option]
    
    NotifyCustomerRejection --> CustomerReupload{Customer<br/>Action?}
    
    CustomerReupload -->|Re-upload Proof| WaitingProof
    CustomerReupload -->|Cancel Order| CancelOrder[Status: 'dibatalkan'<br/>Restore stock]
    CancelOrder --> EndCancelled([Order Cancelled])
    
    %% COD FLOW
    CheckPaymentMethod -->|COD| WaitingCODApproval[Status: 'pending'<br/>Payment: 'unpaid'<br/>Waiting seller<br/>approve COD order]
    
    WaitingCODApproval --> SellerCODDecision{Seller<br/>Approve COD?}
    
    SellerCODDecision -->|Approve| UpdateCODProcessing[Status: 'diproses'<br/>Payment: 'unpaid'<br/>Will be paid on delivery]
    
    SellerCODDecision -->|Reject| RejectCOD[Status: 'dibatalkan'<br/>Payment: 'unpaid'<br/>Restore stock]
    
    RejectCOD --> NotifyCODRejection[Notification Customer:<br/>'Pesanan COD ditolak'<br/>+ Alasan]
    
    NotifyCODRejection --> EndCancelled
    
    %% PROCESSING STAGE
    UpdateToProcessing --> SellerPrepareGoods[Seller Actions:<br/>- Pack products<br/>- Print invoice<br/>- Prepare shipping]
    
    UpdateCODProcessing --> SellerPrepareGoods
    
    SellerPrepareGoods --> SellerReady{Goods<br/>Ready to Ship?}
    
    SellerReady -->|Yes| SellerUpdateShipping[Seller Update:<br/>Status = 'dikirim'<br/>Input resi number<br/>Select courier]
    
    SellerUpdateShipping --> UpdateToShipped[Status: 'dikirim'<br/>Payment: 'verified' (Transfer)<br/>or 'unpaid' (COD)<br/>Tracking number added]
    
    UpdateToShipped --> NotifyCustomerShipped[Notification Customer:<br/>'Pesanan sedang dikirim'<br/>+ Resi tracking<br/>+ Estimated arrival]
    
    NotifyCustomerShipped --> WaitingDelivery[Order in Transit<br/>Customer tracking<br/>via resi number]
    
    WaitingDelivery --> GoodsArrived{Goods<br/>Arrived?}
    
    GoodsArrived -->|Still in Transit| WaitingDelivery
    
    %% DELIVERY CONFIRMATION
    GoodsArrived -->|Arrived| CheckPaymentMethodDelivery{Payment<br/>Method?}
    
    CheckPaymentMethodDelivery -->|Transfer - Already Paid| CustomerConfirm[Customer Klik:<br/>'Pesanan Diterima']
    
    CustomerConfirm --> UpdateToCompleted[Status: 'selesai'<br/>Payment: 'verified'<br/>completed_at = NOW]
    
    CheckPaymentMethodDelivery -->|COD - Pay Now| CustomerPayCOD[Customer Pay Cash<br/>to Courier/Seller]
    
    CustomerPayCOD --> SellerConfirmCODPayment[Seller/Courier Confirm:<br/>Payment received]
    
    SellerConfirmCODPayment --> UpdateCODCompleted[Status: 'selesai'<br/>Payment: 'paid'<br/>paid_at = NOW<br/>completed_at = NOW]
    
    UpdateToCompleted --> UpdateProductSold[UPDATE products<br/>SET sold = sold + qty<br/>WHERE product_id IN (order_items)]
    
    UpdateCODCompleted --> UpdateProductSold
    
    UpdateProductSold --> UpdateSellerRevenue[Update Seller Statistics:<br/>- Total revenue<br/>- Total orders completed<br/>- Success rate]
    
    UpdateSellerRevenue --> TriggerReviewRequest[Optional:<br/>Send Review Request<br/>to Customer]
    
    TriggerReviewRequest --> EndCompleted([Order Completed<br/>Transaction Success])
    
    %% AUTO COMPLETE (Optional)
    WaitingDelivery -.->|After X days| AutoComplete[Auto-complete<br/>if no customer action<br/>Default: 7 days after shipped]
    
    AutoComplete --> UpdateToCompleted
    
    %% CUSTOMER COMPLAINT FLOW
    GoodsArrived -->|Problem/Complaint| CustomerComplain[Customer Report Issue:<br/>- Barang rusak<br/>- Salah kirim<br/>- Tidak sesuai]
    
    CustomerComplain --> SellerResolveComplaint{Seller<br/>Handle Complaint}
    
    SellerResolveComplaint -->|Resolved| CustomerConfirm
    SellerResolveComplaint -->|Refund/Return| InitiateReturn[Status: 'return/refund'<br/>Manual process<br/>by admin/seller]
    
    InitiateReturn --> EndReturn([Order Return/Refund])
    
    style Start fill:#e1f5ff
    style EndCompleted fill:#c8e6c9
    style EndCancelled fill:#ffccbc
    style EndReturn fill:#fff9c4
    style UpdateToProcessing fill:#c8e6c9
    style UpdateToShipped fill:#b2dfdb
    style UpdateToCompleted fill:#c8e6c9
    style RejectPayment fill:#ffccbc
    style RejectCOD fill:#ffccbc
    style WaitingProof fill:#fff9c4
    style WaitingDelivery fill:#fff9c4
```

## Penjelasan Detail Lifecycle:

### **STATUS MATRIX - Complete Overview**

| Status | Payment Status | Description | Customer Action | Seller Action |
|--------|---------------|-------------|-----------------|---------------|
| **pending** | unpaid | Order created, waiting payment/proof | Upload proof (Transfer) or Wait approval (COD) | Wait for proof or Approve COD |
| **menunggu_validasi** | paid | Proof uploaded, waiting validation | Wait | Validate proof (Approve/Reject) |
| **diproses** | verified (Transfer) / unpaid (COD) | Payment confirmed, preparing goods | Track order | Prepare & pack goods |
| **dikirim** | verified (Transfer) / unpaid (COD) | Goods shipped, in transit | Track shipment, Confirm arrival | Update tracking info |
| **selesai** | verified (Transfer) / paid (COD) | Order completed successfully | Optional: Give review | - |
| **dibatalkan** | failed / unpaid | Order cancelled | - | Stock restored |

---

## FASE 1: Order Creation & Payment

### **Initial State:**
```php
Order::create([
    'order_code' => generateOrderCode(), // ORD-20260608-001
    'customer_id' => Auth::id(),
    'store_id' => $storeId,
    'total_price' => $totalPrice,
    'shipping_address' => $address,
    'payment_method' => 'transfer', // or 'cod'
    'payment_status' => 'unpaid',
    'status' => 'pending',
    'created_at' => now()
]);
```

### **Payment Method Split:**

#### **A. Transfer/E-wallet:**
```
Workflow:
1. Order created → status: 'pending', payment: 'unpaid'
2. Customer uploads proof → status: 'menunggu_validasi', payment: 'paid'
3. Seller validates → status: 'diproses', payment: 'verified'
```

#### **B. COD:**
```
Workflow:
1. Order created → status: 'pending', payment: 'unpaid'
2. Seller approves → status: 'diproses', payment: 'unpaid'
3. Delivery + Payment → status: 'selesai', payment: 'paid'
```

---

## FASE 2: Payment Validation

### **Transfer Flow - Detailed:**

**Step 1: Customer Upload Proof**
```php
public function uploadProof($orderId, $proofFile)
{
    $order = Order::findOrFail($orderId);
    
    // Validate file
    $this->validate([
        'proof' => 'required|image|max:2048'
    ]);
    
    // Store proof image
    $proofPath = $proofFile->store('proofs', 'public');
    
    // Update order
    $order->update([
        'proof_of_transfer' => $proofPath,
        'payment_status' => 'paid',
        'status' => 'menunggu_validasi',
        'proof_uploaded_at' => now()
    ]);
    
    // Notify seller
    event(new OrderPaymentUploaded($order));
    
    return 'Bukti transfer berhasil diunggah';
}
```

**Step 2: Seller Validation**
```php
public function approvePayment($orderId)
{
    $order = Order::where('store_id', Auth::user()->store->id)
                  ->findOrFail($orderId);
    
    // Manual check by seller (outside system)
    // Seller verifies in their bank app
    
    // After verification, approve
    $order->update([
        'payment_status' => 'verified',
        'status' => 'diproses',
        'verified_at' => now(),
        'verified_by' => Auth::id()
    ]);
    
    // Notify customer
    event(new OrderStatusUpdated($order));
    
    return 'Pembayaran berhasil diverifikasi';
}
```

**Step 3: Rejection Handling**
```php
public function rejectPayment($orderId, $reason)
{
    $order = Order::findOrFail($orderId);
    
    $order->update([
        'payment_status' => 'failed',
        'status' => 'pending', // Back to pending
        'rejection_reason' => $reason,
        'rejected_at' => now()
    ]);
    
    // Customer can re-upload
    event(new OrderPaymentRejected($order));
    
    return 'Pembayaran ditolak';
}
```

### **COD Flow - Detailed:**

**Step 1: Seller Approve COD Order**
```php
public function approveCODOrder($orderId)
{
    $order = Order::where('payment_method', 'cod')
                  ->findOrFail($orderId);
    
    $order->update([
        'status' => 'diproses',
        'payment_status' => 'unpaid', // Still unpaid until delivery
        'cod_approved_at' => now()
    ]);
    
    event(new OrderStatusUpdated($order));
    
    return 'Pesanan COD disetujui';
}
```

---

## FASE 3: Processing & Shipping

### **Seller Prepares Goods:**

```php
// Seller actions (manual):
1. Check order items
2. Pack products
3. Print invoice: 
   GET /seller/orders/{orderId}/invoice/pdf
4. Prepare shipping label
```

### **Update to Shipped:**

```php
public function updateShippingInfo($orderId, $data)
{
    $order = Order::findOrFail($orderId);
    
    $this->validate([
        'courier' => 'required|string',
        'tracking_number' => 'required|string',
        'estimated_arrival' => 'nullable|date'
    ]);
    
    $order->update([
        'status' => 'dikirim',
        'courier' => $data['courier'],
        'tracking_number' => $data['tracking_number'],
        'estimated_arrival' => $data['estimated_arrival'],
        'shipped_at' => now()
    ]);
    
    // Notify customer
    event(new OrderShipped($order));
    
    // Email with tracking info
    Mail::to($order->customer->email)
        ->queue(new OrderShippedMail($order));
    
    return 'Status pengiriman diperbarui';
}
```

**Customer Notification:**
```
Subject: Pesanan Anda Sedang Dikirim

Order #ORD-20260608-001

Pesanan Anda telah dikirim!

Kurir: JNE REG
No. Resi: JNE1234567890
Estimasi Tiba: 2026-06-10

Track paket Anda:
https://www.jne.co.id/tracking?awb=JNE1234567890
```

---

## FASE 4: Delivery & Completion

### **Customer Confirms Receipt:**

```php
public function confirmReceived($orderId)
{
    $order = Order::where('customer_id', Auth::id())
                  ->where('status', 'dikirim')
                  ->findOrFail($orderId);
    
    DB::transaction(function() use ($order) {
        // Update order status
        $order->update([
            'status' => 'selesai',
            'payment_status' => 'verified', // Already verified for transfer
            'completed_at' => now()
        ]);
        
        // Update product sold count
        foreach ($order->items as $item) {
            $item->product->increment('sold', $item->qty);
        }
        
        // Update seller statistics
        $this->updateSellerStats($order);
        
        // Trigger completion event
        event(new OrderCompleted($order));
    });
    
    return 'Terima kasih! Pesanan selesai';
}
```

### **COD Payment Confirmation:**

```php
public function confirmCODPayment($orderId)
{
    // Called by seller/courier after receiving cash payment
    $order = Order::where('payment_method', 'cod')
                  ->where('status', 'dikirim')
                  ->findOrFail($orderId);
    
    $order->update([
        'payment_status' => 'paid', // Now paid
        'status' => 'selesai',
        'paid_at' => now(),
        'completed_at' => now()
    ]);
    
    // Continue with product sold update...
    
    return 'Pembayaran COD dikonfirmasi';
}
```

### **Auto-Complete (Optional):**

```php
// Laravel scheduled task
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {
        // Auto-complete orders shipped > 7 days ago
        Order::where('status', 'dikirim')
             ->where('shipped_at', '<', now()->subDays(7))
             ->each(function ($order) {
                 $order->update([
                     'status' => 'selesai',
                     'completed_at' => now(),
                     'auto_completed' => true
                 ]);
                 
                 // Update product sold
                 foreach ($order->items as $item) {
                     $item->product->increment('sold', $item->qty);
                 }
             });
    })->daily();
}
```

---

## FASE 5: Post-Completion

### **Update Product Statistics:**

```php
private function updateSellerStats($order)
{
    $store = $order->store;
    
    // Recalculate store statistics
    $stats = [
        'total_orders' => Order::where('store_id', $store->id)
                               ->where('status', 'selesai')
                               ->count(),
        
        'total_revenue' => Order::where('store_id', $store->id)
                                ->where('status', 'selesai')
                                ->sum('total_price'),
        
        'success_rate' => $this->calculateSuccessRate($store->id)
    ];
    
    // Cache stats for dashboard
    Cache::put("store_stats_{$store->id}", $stats, now()->addHours(1));
}
```

### **Review Request (Optional):**

```php
// Send after X days of completion
event(new OrderCompleted($order));

// Listener
class SendReviewRequest
{
    public function handle(OrderCompleted $event)
    {
        $order = $event->order;
        
        // Wait 1 day before sending review request
        Mail::to($order->customer->email)
            ->later(now()->addDay(), new RequestReviewMail($order));
    }
}
```

---

## Status Transition Rules:

### **Allowed Transitions:**

```php
$allowedTransitions = [
    'pending' => ['menunggu_validasi', 'diproses', 'dibatalkan'],
    'menunggu_validasi' => ['diproses', 'pending', 'dibatalkan'],
    'diproses' => ['dikirim', 'dibatalkan'],
    'dikirim' => ['selesai'],
    'selesai' => [], // Terminal state
    'dibatalkan' => [] // Terminal state
];
```

### **Validation Before Transition:**

```php
public function updateStatus($orderId, $newStatus)
{
    $order = Order::findOrFail($orderId);
    $currentStatus = $order->status;
    
    // Check if transition is allowed
    if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
        throw new \Exception("Cannot transition from {$currentStatus} to {$newStatus}");
    }
    
    // Additional validations based on new status
    if ($newStatus === 'dikirim' && empty($order->tracking_number)) {
        throw new \Exception("Tracking number required untuk status dikirim");
    }
    
    if ($newStatus === 'selesai' && $order->payment_status !== 'verified') {
        throw new \Exception("Payment must be verified before completion");
    }
    
    // Proceed with update
    $order->update(['status' => $newStatus]);
}
```

---

## Database State Changes:

### **Order Creation:**
```sql
INSERT INTO orders (
    order_code, customer_id, store_id,
    total_price, shipping_address, payment_method,
    payment_status, status, created_at
) VALUES (
    'ORD-20260608-001', 1, 5,
    150000, 'Jl. Contoh No. 123', 'transfer',
    'unpaid', 'pending', NOW()
);
```

### **Proof Upload:**
```sql
UPDATE orders 
SET 
    proof_of_transfer = '/storage/proofs/abc123.jpg',
    payment_status = 'paid',
    status = 'menunggu_validasi',
    proof_uploaded_at = NOW()
WHERE id = {order_id};
```

### **Payment Approval:**
```sql
UPDATE orders 
SET 
    payment_status = 'verified',
    status = 'diproses',
    verified_at = NOW(),
    verified_by = {seller_user_id}
WHERE id = {order_id};
```

### **Shipping Update:**
```sql
UPDATE orders 
SET 
    status = 'dikirim',
    courier = 'JNE REG',
    tracking_number = 'JNE1234567890',
    estimated_arrival = '2026-06-10',
    shipped_at = NOW()
WHERE id = {order_id};
```

### **Completion:**
```sql
UPDATE orders 
SET 
    status = 'selesai',
    completed_at = NOW()
WHERE id = {order_id};

-- Update product sold count
UPDATE products p
JOIN order_items oi ON p.id = oi.product_id
SET p.sold = p.sold + oi.qty
WHERE oi.order_id = {order_id};
```

---

## Event-Driven Architecture:

### **Events & Listeners:**

```php
// Events
OrderCreated          → NotifySellerNewOrder
OrderPaymentUploaded  → NotifySellerProofReceived
OrderStatusUpdated    → NotifyCustomerStatusChange
OrderShipped          → SendTrackingInfoEmail
OrderCompleted        → UpdateStatistics, SendReviewRequest
OrderPaymentRejected  → NotifyCustomerRejection

// Event dispatch
event(new OrderStatusUpdated($order));

// Listener
class NotifyCustomerStatusChange
{
    public function handle(OrderStatusUpdated $event)
    {
        $order = $event->order;
        
        // Real-time notification via Echo
        broadcast(new OrderUpdated($order))->toOthers();
        
        // Email notification
        Mail::to($order->customer->email)
            ->queue(new OrderStatusChangedMail($order));
    }
}
```

---

## Business Rules & Edge Cases:

### ✅ **Success Path:**
```
Transfer: pending → menunggu_validasi → diproses → dikirim → selesai
COD: pending → diproses → dikirim → selesai
```

### ❌ **Failure Paths:**
```
Payment Rejected: menunggu_validasi → pending (can re-upload)
COD Rejected: pending → dibatalkan
Customer Cancel: pending → dibatalkan
```

### ⚠️ **Edge Cases:**
1. **Customer never uploads proof**: Order stuck in `pending`
   - Solution: Auto-cancel after X days
   
2. **Seller never validates proof**: Order stuck in `menunggu_validasi`
   - Solution: Auto-approve after X days (risky) or remind seller
   
3. **Customer never confirms received**: Order stuck in `dikirim`
   - Solution: Auto-complete after 7 days
   
4. **COD customer refuses payment**: Manual handling required
   - Solution: Return to store, manual cancellation

5. **Product out of stock after order**: Stock already reduced at checkout
   - Solution: Seller must fulfill or refund (manual process)

---

## Metrics & Analytics:

### **Order Funnel:**
```
Total Orders Created
├─ Pending (waiting proof/approval): X%
├─ Payment Validated: Y%
├─ In Processing: Z%
├─ Shipped: A%
├─ Completed: B%
└─ Cancelled: C%

Success Rate = Completed / Total Orders × 100%
```

### **Average Time per Stage:**
```php
// Calculate average time from order to completion
$avgCompletionTime = Order::where('status', 'selesai')
    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
    ->value('avg_hours');

// Breakdown by stage
$avgValidationTime = Order::whereNotNull('verified_at')
    ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, proof_uploaded_at, verified_at)) as avg_hours')
    ->value('avg_hours');
```
