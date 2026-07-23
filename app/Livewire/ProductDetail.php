<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.app')]
class ProductDetail extends Component
{
    public string $slug;
    public int $qty = 1;

    public function mount(string $slug)
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $product = Product::with(['category', 'store'])
            ->withSoldQuantity()
            ->where('slug', $this->slug)
            ->whereHas('store', function($q) {
            $q->where('status', 'approved');
        })->firstOrFail();

        $relatedProducts = Product::query()->where('category_id', $product->category_id)
            ->withSoldQuantity()
            ->where('id', '!=', $product->id)
            ->whereHas('store', function($q) {
                $q->where('status', 'approved');
            })
            ->limit(4)
            ->get();

        return view('livewire.product-detail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }

    public function addToCart()
    {
        if (!Auth::check()) {
            $this->dispatch('show-login-modal', nonce: uniqid('login_', true));
            return;
        }

        if (Auth::user()->role !== 'customer') {
            $this->dispatch('toast', message: 'Hanya akun customer yang dapat berbelanja.', type: 'error');
            return;
        }

        $product = Product::query()->where('slug', $this->slug)
            ->whereHas('store', function($q) {
                $q->where('status', 'approved');
            })
            ->firstOrFail();
        
        if ($product->stock <= 0) {
            $this->dispatch('toast', message: 'Maaf, stok produk ini sudah habis!', type: 'error');
            return;
        }

        $cartItem = \App\Models\CartItem::firstOrNew([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
        ]);

        $newQty = $cartItem->exists ? $cartItem->qty + $this->qty : $this->qty;
        
        if ($newQty > $product->stock) {
            $this->dispatch('toast', message: 'Stok tidak mencukupi! Maksimal ' . $product->stock . ' pcs.', type: 'warning');
            $cartItem->qty = $product->stock;
        } else {
            $cartItem->qty = $newQty;
        }

        $cartItem->save();

        $this->dispatch('cart-updated')->to(CartManager::class);
        $this->dispatch('cart-badge-bounce');
        $this->dispatch('toast', message: 'Produk ditambahkan ke keranjang!', type: 'success');
    }

    public function buyNow()
    {
        if (!Auth::check()) {
            $this->dispatch('show-login-modal', nonce: uniqid('login_', true));
            return;
        }

        if (Auth::user()->role !== 'customer') {
            $this->dispatch('toast', message: 'Hanya akun customer yang dapat berbelanja.', type: 'error');
            return;
        }

        $product = Product::query()->where('slug', $this->slug)
            ->whereHas('store', function($q) {
                $q->where('status', 'approved');
            })
            ->firstOrFail();

        if ($product->stock <= 0) {
            $this->dispatch('toast', message: 'Maaf, stok produk ini sudah habis!', type: 'error');
            return;
        }

        $qty = max(1, min($this->qty, $product->stock));
        $this->qty = $qty;

        return redirect()->route('checkout', [
            'product_id' => $product->id,
            'qty' => $qty,
        ]);
    }
}
