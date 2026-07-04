<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class CartManager extends Component
{
    public int $cartCount = 0;

    public function mount()
    {
        $this->refreshCount();
    }

    #[On('cart-updated')]
    public function refreshCount()
    {
        if (Auth::check()) {
            $this->cartCount = CartItem::where('user_id', Auth::id())->sum('qty');
        } else {
            $this->cartCount = 0;
        }
    }

    public function render()
    {
        return view('livewire.cart-manager');
    }
}
