<?php

namespace App\Livewire\Seller;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Products extends Component
{
    use WithFileUploads;

    public string $view = 'list'; // can be 'list', 'create', 'edit'
    public string $search = '';

    // Form fields
    public ?int $productId = null;
    public string $name = '';
    public string $description = '';
    public string $category_id = '';
    public string $price = '';
    public string $stock = '';
    public $image;
    public ?string $existingImage = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|max:100',
            'description' => 'required|string|min:10|max:5000',
            'category_id' => 'required|exists:categories,id',
            'price' => [
                'required',
                'numeric',
                'min:100',
                'max:999999999', // 🔒 SECURITY FIX: Max limit (ISSUE-007)
                'regex:/^\d+$/' // Only integers
            ],
            'stock' => [
                'required',
                'integer',
                'min:0',
                'max:999999' // 🔒 SECURITY FIX: Max limit (ISSUE-007)
            ],
            'image' => [
                $this->productId ? 'nullable' : 'required',
                'image',
                'mimes:jpeg,jpg,png,webp', // 🔒 SECURITY FIX: Extension whitelist (ISSUE-004)
                'max:2048', // 🔒 SECURITY FIX: 2MB max (ISSUE-004)
                'dimensions:min_width=300,min_height=300,max_width=4096,max_height=4096' // 🔒 SECURITY FIX (ISSUE-004, ISSUE-027)
            ],
        ];
    }

    protected $messages = [
        'name.required' => 'Nama produk wajib diisi.',
        'description.required' => 'Deskripsi wajib diisi.',
        'category_id.required' => 'Kategori wajib diisi.',
        'price.required' => 'Harga wajib diisi.',
        'stock.required' => 'Stok wajib diisi.',
        'image.required' => 'Gambar produk wajib diunggah.',
        'image.image' => 'File harus berupa gambar.',
    ];

    public function showCreateForm()
    {
        $this->resetForm();
        $this->view = 'create';
    }

    public function showEditForm(int $id)
    {
        $this->resetForm();
        $product = Product::where('store_id', Auth::user()->store->id)->findOrFail($id);
        
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->category_id = $product->category_id;
        $this->price = $product->price;
        $this->stock = $product->stock;
        $this->existingImage = $product->image;

        $this->view = 'edit';
    }

    public function cancel()
    {
        $this->resetForm();
        $this->view = 'list';
    }

    private function resetForm()
    {
        $this->productId = null;
        $this->name = '';
        $this->description = '';
        $this->category_id = '';
        $this->price = '';
        $this->stock = '';
        $this->image = null;
        $this->existingImage = null;
        $this->resetValidation();
    }

    public function saveProduct()
    {
        $this->validate();

        $store = Auth::user()->store;
        if (!$store || $store->status !== 'approved') {
            session()->flash('error', 'Toko Anda belum disetujui.');
            return;
        }

        $imagePath = $this->existingImage;
        if ($this->image) {
            // Delete old image if exists
            if ($this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            
            // 🔒 SECURITY FIX: Generate secure random filename (ISSUE-004)
            $extension = $this->image->getClientOriginalExtension();
            $filename = Str::uuid() . '.' . $extension;
            
            // Store with controlled name
            $imagePath = $this->image->storeAs('products', $filename, 'public');
            
            if ($imagePath) {
                $fullPath = Storage::disk('public')->path($imagePath);
                
                // 🔒 SECURITY FIX: Verify MIME type after upload (ISSUE-004)
                $mimeType = Storage::disk('public')->mimeType($imagePath);
                if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
                    Storage::disk('public')->delete($imagePath);
                    throw new \Exception('Invalid file type detected');
                }
                
                // Compress image
                \App\Helpers\ImageCompressor::compressPath($fullPath);
            }
        }

        $data = [
            'store_id' => $store->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => Str::slug($this->name) . '-' . Str::random(4),
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'image' => $imagePath,
        ];

        if ($this->productId) {
            $product = Product::where('store_id', $store->id)->findOrFail($this->productId);
            // keep old slug if name hasn't changed or just overwrite slug
            if ($product->name !== $this->name) {
                $product->update($data);
            } else {
                unset($data['slug']);
                $product->update($data);
            }
            session()->flash('success', 'Produk berhasil diubah.');
        } else {
            Product::create($data);
            session()->flash('success', 'Produk berhasil ditambahkan.');
        }

        $this->resetForm();
        $this->view = 'list';
    }

    public function deleteProduct(int $id)
    {
        $store = Auth::user()->store;
        $product = Product::where('store_id', $store->id)->findOrFail($id);
        
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        session()->flash('success', 'Produk berhasil dihapus.');
    }

    public function render()
    {
        $store = Auth::user()->store;
        if (!$store) {
            abort(403);
        }

        $products = Product::with('category')
            ->withSoldQuantity()
            ->where('store_id', $store->id)
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $categories = Category::all();

        return view('livewire.seller.products', [
            'products' => $products,
            'categories' => $categories,
            'store' => $store,
        ])->extends('layouts.dashboard')->section('content');
    }
}
