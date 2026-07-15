<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Store;

#[Layout('layouts.app')]
class StoreDetail extends Component
{
    public string $slug;

    public function mount(string $slug)
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $store = Store::query()->where('slug', $this->slug)->where('status', 'approved')->firstOrFail();
        $products = $store->products()
            ->with('category')
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->withSoldQuantity()
            ->latest()
            ->get();

        return view('livewire.store-detail', [
            'store' => $store,
            'products' => $products
        ]);
    }
}
