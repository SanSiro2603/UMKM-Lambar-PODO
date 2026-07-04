# Activity Diagram - Product Management (Seller)

## Proses: CRUD Produk oleh Seller

```mermaid
flowchart TD
    Start([Seller Login ke Dashboard]) --> CheckStoreStatus{Status Toko<br/>Approved?}
    
    CheckStoreStatus -->|Pending/Rejected| ShowStoreError[Tampilkan Error:<br/>'Toko belum disetujui admin'<br/>Fitur produk disabled]
    ShowStoreError --> End1([Cannot Manage Products])
    
    CheckStoreStatus -->|Approved| AccessProductMenu[Seller Klik Menu<br/>'Kelola Produk']
    
    AccessProductMenu --> LoadProductList[Load /seller/products<br/>Query: products<br/>WHERE store_id = seller_store_id<br/>JOIN categories]
    
    LoadProductList --> DisplayProductTable[Tampilkan Tabel Produk:<br/>- Foto thumbnail<br/>- Nama produk<br/>- Kategori<br/>- Harga<br/>- Stok<br/>- Status<br/>- Actions: Edit/Delete]
    
    DisplayProductTable --> SellerAction{Seller<br/>Pilih Aksi?}
    
    %% CREATE FLOW
    SellerAction -->|Tambah Produk Baru| ClickAddProduct[Klik Tombol<br/>'+ Tambah Produk']
    
    ClickAddProduct --> ShowCreateForm[Tampilkan Form Modal/Page:<br/>- Nama produk*<br/>- Kategori* (dropdown)<br/>- Deskripsi<br/>- Harga*<br/>- Stok*<br/>- Upload foto*]
    
    ShowCreateForm --> FillProductForm[Seller Isi Form]
    
    FillProductForm --> UploadProductImage[Upload Foto Produk<br/>Max 2MB<br/>Format: jpg, png, webp]
    
    UploadProductImage --> SubmitCreate[Klik 'Simpan']
    
    SubmitCreate --> ValidateCreateForm{Validasi<br/>Form Input?}
    
    ValidateCreateForm -->|Invalid| ShowCreateError[Error:<br/>- Field required kosong<br/>- Harga < 0<br/>- Stok < 0<br/>- File size > 2MB]
    ShowCreateError --> ShowCreateForm
    
    ValidateCreateForm -->|Valid| CompressImage[Compress Image<br/>Using ImageCompressor<br/>Max width: 1200px<br/>Quality: 80%]
    
    CompressImage --> GenerateSlug[Generate Slug<br/>dari nama produk<br/>+ unique suffix]
    
    GenerateSlug --> InsertProduct[INSERT INTO products:<br/>- store_id = current_store<br/>- category_id = selected<br/>- name, slug<br/>- description<br/>- price, stock<br/>- image = compressed_path<br/>- rating = 0, sold = 0]
    
    InsertProduct --> ShowCreateSuccess[Success Message:<br/>'Produk berhasil ditambahkan']
    
    ShowCreateSuccess --> RefreshList[Refresh Product List<br/>Livewire: $refresh]
    
    RefreshList --> DisplayProductTable
    
    %% READ/VIEW FLOW
    SellerAction -->|Lihat Detail| ClickViewProduct[Klik Icon 'View']
    
    ClickViewProduct --> ShowProductDetail[Modal/Page Detail:<br/>- Full image preview<br/>- All product data<br/>- Statistics:<br/>  * Total sold<br/>  * Revenue from this product<br/>  * Average rating]
    
    ShowProductDetail --> DisplayProductTable
    
    %% UPDATE FLOW
    SellerAction -->|Edit Produk| ClickEditProduct[Klik Icon 'Edit']
    
    ClickEditProduct --> LoadProductData[Load Product Data<br/>SELECT * FROM products<br/>WHERE id = product_id<br/>AND store_id = current_store]
    
    LoadProductData --> ShowEditForm[Tampilkan Form<br/>Pre-filled dengan data lama:<br/>- Nama<br/>- Kategori<br/>- Deskripsi<br/>- Harga<br/>- Stok<br/>- Foto existing]
    
    ShowEditForm --> SellerEditFields[Seller Ubah Data<br/>yang Ingin Diupdate]
    
    SellerEditFields --> CheckImageUpdate{Upload<br/>Foto Baru?}
    
    CheckImageUpdate -->|Ya| UploadNewImage[Upload & Compress<br/>Foto Baru]
    UploadNewImage --> DeleteOldImage[Delete Old Image<br/>dari Storage]
    DeleteOldImage --> SubmitUpdate
    
    CheckImageUpdate -->|Tidak| SubmitUpdate[Klik 'Update']
    
    SubmitUpdate --> ValidateUpdateForm{Validasi<br/>Form Update?}
    
    ValidateUpdateForm -->|Invalid| ShowUpdateError[Error:<br/>- Harga tidak valid<br/>- Stok tidak valid<br/>- Nama kosong]
    ShowUpdateError --> ShowEditForm
    
    ValidateUpdateForm -->|Valid| UpdateSlugIfNeeded{Nama Produk<br/>Berubah?}
    
    UpdateSlugIfNeeded -->|Ya| RegenerateSlug[Generate Slug Baru<br/>dari nama baru]
    RegenerateSlug --> UpdateProduct
    
    UpdateSlugIfNeeded -->|Tidak| UpdateProduct[UPDATE products SET<br/>category_id, name, slug<br/>description, price, stock<br/>image, updated_at<br/>WHERE id = product_id<br/>AND store_id = current_store]
    
    UpdateProduct --> ShowUpdateSuccess[Success Message:<br/>'Produk berhasil diperbarui']
    
    ShowUpdateSuccess --> RefreshList
    
    %% DELETE FLOW
    SellerAction -->|Hapus Produk| ClickDeleteProduct[Klik Icon 'Delete']
    
    ClickDeleteProduct --> ShowDeleteConfirm[Tampilkan Konfirmasi:<br/>'Yakin hapus produk ini?'<br/>Nama: {product_name}<br/>Warning: Data tidak bisa<br/>dikembalikan]
    
    ShowDeleteConfirm --> ConfirmDelete{Seller<br/>Konfirmasi?}
    
    ConfirmDelete -->|Cancel| DisplayProductTable
    
    ConfirmDelete -->|Confirm| CheckProductInOrders{Cek: Produk<br/>Ada di Order<br/>Aktif?}
    
    CheckProductInOrders -->|Ya, Ada Order| ShowDeleteWarning[Warning:<br/>'Produk sedang ada di order<br/>yang belum selesai'<br/>Options:<br/>- Soft delete<br/>- Cancel anyway]
    
    ShowDeleteWarning --> SoftDeleteOption{Pilih<br/>Opsi?}
    
    SoftDeleteOption -->|Soft Delete| MarkAsInactive[UPDATE products<br/>SET status = 'inactive'<br/>atau is_deleted = true]
    
    MarkAsInactive --> HideFromPublic[Produk disembunyikan<br/>dari Public Storefront<br/>tapi data tetap ada<br/>untuk order existing]
    
    HideFromPublic --> ShowSoftDeleteSuccess[Success:<br/>'Produk dinonaktifkan']
    
    ShowSoftDeleteSuccess --> RefreshList
    
    SoftDeleteOption -->|Force Delete| ProceedForceDelete[Lanjutkan Hapus Permanen]
    
    CheckProductInOrders -->|Tidak| ProceedForceDelete
    
    ProceedForceDelete --> DeleteProductImage[Delete Product Image<br/>dari Storage:<br/>Storage::delete]
    
    DeleteProductImage --> DeleteCartReferences[DELETE FROM cart_items<br/>WHERE product_id = product_id<br/>CASCADE handled by FK]
    
    DeleteCartReferences --> DeleteProductRecord[DELETE FROM products<br/>WHERE id = product_id<br/>AND store_id = current_store]
    
    DeleteProductRecord --> ShowDeleteSuccess[Success:<br/>'Produk berhasil dihapus']
    
    ShowDeleteSuccess --> RefreshList
    
    %% BULK ACTIONS
    SellerAction -->|Bulk Update Stok| SelectMultipleProducts[Seller Centang<br/>Checkbox Multiple Produk]
    
    SelectMultipleProducts --> ClickBulkStock[Klik 'Update Stok<br/>Massal']
    
    ClickBulkStock --> ShowBulkStockForm[Tampilkan Form:<br/>Tabel product list<br/>dengan input stok baru]
    
    ShowBulkStockForm --> FillBulkStock[Seller Input<br/>Stok Baru per Produk]
    
    FillBulkStock --> SubmitBulkStock[Klik 'Simpan Semua']
    
    SubmitBulkStock --> UpdateBulkStock[LOOP: UPDATE products<br/>SET stock = new_stock<br/>WHERE id IN selected_ids]
    
    UpdateBulkStock --> ShowBulkSuccess[Success:<br/>'{count} produk berhasil<br/>diperbarui']
    
    ShowBulkSuccess --> RefreshList
    
    style Start fill:#e1f5ff
    style End1 fill:#ffccbc
    style ShowStoreError fill:#ffccbc
    style ShowCreateError fill:#ffccbc
    style ShowUpdateError fill:#ffccbc
    style ShowDeleteWarning fill:#fff9c4
    style InsertProduct fill:#b2dfdb
    style UpdateProduct fill:#b2dfdb
    style DeleteProductRecord fill:#ffccbc
    style ShowCreateSuccess fill:#c8e6c9
    style ShowUpdateSuccess fill:#c8e6c9
    style ShowDeleteSuccess fill:#c8e6c9
```

## Penjelasan Detail Alur:

### **FASE 0: Prerequisites Check**

#### **Store Approval Validation:**
```php
// Middleware or controller check
if (Auth::user()->store->status !== 'approved') {
    return redirect()->route('seller.dashboard')
        ->with('error', 'Toko Anda belum disetujui oleh admin');
}
```

**Business Rule:**
- ❌ Status `pending` → Cannot manage products
- ❌ Status `rejected` → Cannot manage products
- ✅ Status `approved` → Full access to product management

---

### **FASE 1: CREATE Product**

#### **Form Validation:**
```php
$rules = [
    'name' => 'required|string|max:255',
    'category_id' => 'required|exists:categories,id',
    'description' => 'nullable|string|max:1000',
    'price' => 'required|integer|min:100', // Min Rp 100
    'stock' => 'required|integer|min:0',
    'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048' // 2MB
];
```

#### **Image Processing:**
```php
use App\Helpers\ImageCompressor;

// Upload and compress
$image = $request->file('image');
$compressedPath = ImageCompressor::compress($image, [
    'max_width' => 1200,
    'max_height' => 1200,
    'quality' => 80
]);

// Store in storage/app/public/products
$finalPath = $compressedPath->store('products', 'public');
```

#### **Slug Generation:**
```php
use Illuminate\Support\Str;

$baseSlug = Str::slug($request->name);
$slug = $baseSlug;
$counter = 1;

// Ensure unique slug
while (Product::where('slug', $slug)->exists()) {
    $slug = $baseSlug . '-' . $counter;
    $counter++;
}

// Example: "kopi-arabika" or "kopi-arabika-2"
```

#### **Product Creation:**
```php
$product = Product::create([
    'store_id' => Auth::user()->store->id,
    'category_id' => $request->category_id,
    'name' => $request->name,
    'slug' => $slug,
    'description' => $request->description,
    'price' => $request->price,
    'stock' => $request->stock,
    'image' => $finalPath,
    'rating' => 0.00,
    'sold' => 0
]);
```

---

### **FASE 2: READ Product List**

#### **Query with Eager Loading:**
```php
$products = Product::where('store_id', Auth::user()->store->id)
    ->with('category')
    ->withCount('orderItems')
    ->withSum('soldOrderItems as total_sold', 'qty')
    ->orderBy('created_at', 'desc')
    ->paginate(15);
```

#### **Display Table:**
```html
<table>
  <thead>
    <tr>
      <th>Foto</th>
      <th>Nama Produk</th>
      <th>Kategori</th>
      <th>Harga</th>
      <th>Stok</th>
      <th>Terjual</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    @foreach($products as $product)
    <tr>
      <td><img src="{{ $product->image_url }}" width="50"></td>
      <td>{{ $product->name }}</td>
      <td>{{ $product->category->name }}</td>
      <td>Rp {{ number_format($product->price) }}</td>
      <td>{{ $product->stock }} pcs</td>
      <td>{{ $product->total_sold ?? 0 }}</td>
      <td>
        <button wire:click="view({{ $product->id }})">👁️</button>
        <button wire:click="edit({{ $product->id }})">✏️</button>
        <button wire:click="delete({{ $product->id }})">🗑️</button>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
```

---

### **FASE 3: UPDATE Product**

#### **Load Existing Data:**
```php
public function edit($productId)
{
    $this->product = Product::where('id', $productId)
        ->where('store_id', Auth::user()->store->id)
        ->firstOrFail();
    
    // Pre-fill form
    $this->productId = $this->product->id;
    $this->name = $this->product->name;
    $this->category_id = $this->product->category_id;
    $this->description = $this->product->description;
    $this->price = $this->product->price;
    $this->stock = $this->product->stock;
    $this->existingImage = $this->product->image;
    
    $this->showEditModal = true;
}
```

#### **Update Logic:**
```php
public function update()
{
    $this->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'price' => 'required|integer|min:100',
        'stock' => 'required|integer|min:0',
        'newImage' => 'nullable|image|max:2048'
    ]);
    
    $data = [
        'name' => $this->name,
        'category_id' => $this->category_id,
        'description' => $this->description,
        'price' => $this->price,
        'stock' => $this->stock,
        'updated_at' => now()
    ];
    
    // Handle new image upload
    if ($this->newImage) {
        // Delete old image
        Storage::disk('public')->delete($this->product->image);
        
        // Compress and store new image
        $compressedPath = ImageCompressor::compress($this->newImage, [
            'max_width' => 1200,
            'quality' => 80
        ]);
        $data['image'] = $compressedPath->store('products', 'public');
    }
    
    // Regenerate slug if name changed
    if ($this->name !== $this->product->name) {
        $data['slug'] = $this->generateUniqueSlug($this->name);
    }
    
    $this->product->update($data);
    
    session()->flash('message', 'Produk berhasil diperbarui');
    $this->showEditModal = false;
}
```

---

### **FASE 4: DELETE Product**

#### **Soft Delete vs Hard Delete:**

**Check for Active Orders:**
```php
$hasActiveOrders = OrderItem::where('product_id', $productId)
    ->whereHas('order', function($query) {
        $query->whereIn('status', ['pending', 'menunggu_validasi', 'diproses', 'dikirim']);
    })
    ->exists();

if ($hasActiveOrders) {
    // Recommend soft delete
    $this->showSoftDeleteOption = true;
}
```

**Soft Delete (Recommended):**
```php
public function softDelete($productId)
{
    Product::where('id', $productId)
           ->where('store_id', Auth::user()->store->id)
           ->update([
               'status' => 'inactive', // or use deleted_at if using SoftDeletes trait
               'deleted_at' => now()
           ]);
    
    // Product hidden from public but data retained for order history
    session()->flash('message', 'Produk dinonaktifkan');
}
```

**Hard Delete (Force):**
```php
public function forceDelete($productId)
{
    $product = Product::where('id', $productId)
                      ->where('store_id', Auth::user()->store->id)
                      ->firstOrFail();
    
    // Delete image from storage
    Storage::disk('public')->delete($product->image);
    
    // Delete product (CASCADE will handle cart_items, order_items)
    $product->delete();
    
    session()->flash('message', 'Produk berhasil dihapus permanen');
}
```

#### **Cascade Effects:**
```sql
-- When product is deleted:

-- 1. Cart items removed (FK CASCADE)
DELETE FROM cart_items WHERE product_id = {deleted_product_id};

-- 2. Order items retained (for history) but product_id becomes NULL
-- OR: Keep order_items with product snapshot
-- Depends on FK constraint configuration

-- 3. Image file deleted from filesystem
Storage::delete('products/{product_image}');
```

---

### **FASE 5: Bulk Actions**

#### **Bulk Stock Update:**
```php
public function bulkUpdateStock($updates)
{
    // $updates = [
    //     ['id' => 1, 'stock' => 50],
    //     ['id' => 2, 'stock' => 100],
    //     ...
    // ]
    
    DB::transaction(function() use ($updates) {
        foreach ($updates as $update) {
            Product::where('id', $update['id'])
                   ->where('store_id', Auth::user()->store->id)
                   ->update(['stock' => $update['stock']]);
        }
    });
    
    session()->flash('message', count($updates) . ' produk berhasil diperbarui');
}
```

#### **Bulk Delete:**
```php
public function bulkDelete($productIds)
{
    // Validate ownership
    $products = Product::whereIn('id', $productIds)
                       ->where('store_id', Auth::user()->store->id)
                       ->get();
    
    foreach ($products as $product) {
        // Delete image
        Storage::disk('public')->delete($product->image);
        
        // Delete product
        $product->delete();
    }
    
    session()->flash('message', $products->count() . ' produk berhasil dihapus');
}
```

---

## Database State Changes:

### **Create:**
```sql
INSERT INTO products (
    store_id, category_id, name, slug, 
    description, price, stock, image, 
    rating, sold, created_at, updated_at
) VALUES (
    {store_id}, {category_id}, '{name}', '{slug}',
    '{description}', {price}, {stock}, '{image_path}',
    0.00, 0, NOW(), NOW()
);
```

### **Update:**
```sql
UPDATE products 
SET 
    name = '{new_name}',
    category_id = {new_category},
    description = '{new_description}',
    price = {new_price},
    stock = {new_stock},
    image = '{new_image_path}', -- if changed
    slug = '{new_slug}', -- if name changed
    updated_at = NOW()
WHERE id = {product_id}
  AND store_id = {current_store_id};
```

### **Soft Delete:**
```sql
UPDATE products 
SET 
    status = 'inactive',
    deleted_at = NOW()
WHERE id = {product_id}
  AND store_id = {current_store_id};
```

### **Hard Delete:**
```sql
DELETE FROM products 
WHERE id = {product_id}
  AND store_id = {current_store_id};

-- Cascade effects:
-- cart_items: DELETE WHERE product_id = {product_id}
-- order_items: Retained or set product_id = NULL (depends on FK)
```

---

## Security & Business Rules:

### 🔒 **Security:**
1. ✅ Seller can only manage products in their own store
2. ✅ Store must be "approved" to create/edit products
3. ✅ Image validation (type, size, dimensions)
4. ✅ CSRF token on all forms
5. ✅ Rate limiting on create/update endpoints

### 📋 **Business Rules:**
1. ✅ Product name must be unique per store (soft constraint)
2. ✅ Slug must be globally unique
3. ✅ Price minimum: Rp 100
4. ✅ Stock cannot be negative
5. ✅ Category must exist (FK constraint)
6. ✅ Soft delete recommended if product has active orders
7. ✅ Image compression automatic (max 1200px, quality 80%)

### 🎨 **Image Handling:**
```php
// ImageCompressor helper logic
public static function compress($image, $options = [])
{
    $maxWidth = $options['max_width'] ?? 1200;
    $maxHeight = $options['max_height'] ?? 1200;
    $quality = $options['quality'] ?? 80;
    
    $img = Image::make($image);
    
    // Resize if larger than max dimensions
    $img->resize($maxWidth, $maxHeight, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    });
    
    // Compress
    $img->encode('jpg', $quality);
    
    return $img;
}
```

### 📊 **Performance Optimization:**
- Eager loading relationships (`with('category')`)
- Pagination (15 items per page)
- Image lazy loading on frontend
- Index on `store_id` and `slug` columns

---

## Livewire Component Structure:

```php
// app/Livewire/Seller/Products.php
class Products extends Component
{
    public $products;
    public $categories;
    
    // Form properties
    public $productId;
    public $name;
    public $category_id;
    public $description;
    public $price;
    public $stock;
    public $newImage;
    public $existingImage;
    
    // UI state
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteConfirm = false;
    
    // Methods
    public function mount() { /* Load initial data */ }
    public function render() { /* Return view */ }
    public function create() { /* Show create form */ }
    public function store() { /* Insert new product */ }
    public function edit($id) { /* Load edit form */ }
    public function update() { /* Update product */ }
    public function confirmDelete($id) { /* Show delete confirm */ }
    public function delete() { /* Delete product */ }
    public function bulkUpdateStock($updates) { /* Bulk update */ }
}
```

---

## Error Handling:

**Validation Errors:**
```php
try {
    $this->validate($rules);
} catch (ValidationException $e) {
    return $this->addError('validation', $e->getMessage());
}
```

**Image Upload Errors:**
```php
if ($this->newImage->getSize() > 2048000) {
    return $this->addError('newImage', 'File terlalu besar (max 2MB)');
}
```

**Database Errors:**
```php
DB::transaction(function() {
    // Create/Update logic
});

// If fails, automatic rollback
```

**Foreign Key Constraint:**
```php
try {
    $product->delete();
} catch (\Illuminate\Database\QueryException $e) {
    if ($e->getCode() == 23000) { // FK violation
        return $this->addError('delete', 'Produk tidak bisa dihapus karena terkait dengan order');
    }
}
```
