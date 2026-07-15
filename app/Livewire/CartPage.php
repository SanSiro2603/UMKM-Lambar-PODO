<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CartPage extends Component
{
    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses keranjang belanja.');
        }
    }

    #[Computed]
    public function cartItems()
    {
        return CartItem::with(['product.store', 'product.category'])
            ->where('user_id', Auth::id())
            ->whereHas('product.store', function ($query) {
                $query->where('status', 'approved');
            })
            ->get();
    }

    #[Computed]
    public function groupedBySeller()
    {
        $groups = [];
        foreach ($this->cartItems as $item) {
            $storeName = $item->product->store->name;
            if (!isset($groups[$storeName])) {
                $groups[$storeName] = [];
            }
            $groups[$storeName][] = $item;
        }
        return $groups;
    }

    #[Computed]
    public function selectedItems()
    {
        return $this->cartItems->where('selected', true);
    }

    #[Computed]
    public function selectedCount()
    {
        return $this->selectedItems->sum('qty');
    }

    #[Computed]
    public function selectedTotal()
    {
        return $this->selectedItems->sum(function ($item) {
            return $item->qty * $item->product->price;
        });
    }

    #[Computed]
    public function hasSelected()
    {
        return $this->selectedItems->isNotEmpty();
    }

    public function updateQty($itemId, $qty)
    {
        $item = CartItem::find($itemId);
        if ($item && $item->user_id === Auth::id()) {
            $stock = $item->product->stock;
            $newQty = max(1, min($qty, $stock));
            
            if ($qty > $stock) {
                $this->dispatch('toast', message: 'Stok tidak mencukupi! Maksimal ' . $stock . ' pcs.', type: 'warning');
            }
            
            $item->update(['qty' => $newQty]);
            $this->dispatch('cart-updated')->to(CartManager::class);
            $this->dispatch('cart-badge-bounce');
        }
    }

    public function removeItem($itemId)
    {
        $item = CartItem::find($itemId);
        if ($item && $item->user_id === Auth::id()) {
            $item->delete();
            $this->dispatch('cart-updated')->to(CartManager::class);
            $this->dispatch('cart-badge-bounce');
            $this->dispatch('toast', message: 'Produk dihapus dari keranjang', type: 'info');
        }
    }

    public function toggleSelected($itemId)
    {
        $item = CartItem::find($itemId);
        if ($item && $item->user_id === Auth::id()) {
            $item->update(['selected' => !$item->selected]);
        }
    }

    public function isAllSelectedForSeller($storeName)
    {
        $items = $this->groupedBySeller[$storeName] ?? [];
        if (empty($items)) return false;

        foreach ($items as $item) {
            if (!$item->selected) {
                return false;
            }
        }
        return true;
    }

    public function selectAllForSeller($storeName)
    {
        $items = collect($this->groupedBySeller[$storeName] ?? []);
        $itemIds = $items->pluck('id')->toArray();
        CartItem::query()->whereIn('id', $itemIds)->update(['selected' => true]);
    }

    public function deselectAllForSeller($storeName)
    {
        $items = collect($this->groupedBySeller[$storeName] ?? []);
        $itemIds = $items->pluck('id')->toArray();
        CartItem::query()->whereIn('id', $itemIds)->update(['selected' => false]);
    }

    public function checkout()
    {
        if (!$this->hasSelected) {
             $this->dispatch('toast', message: 'Pilih produk untuk di-checkout', type: 'warning');
             return;
        }

        // Simpan data cart yang di-select ke session, kalau diperlukan, atau langsung redirect.
        // Karena kita sekarang baca dari DB (yang is_selected = true), kita bisa langsung redirect saja.
        return redirect()->route('checkout');
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
