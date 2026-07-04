<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Store;

class StoreCatalog extends Component
{
    public string $search = '';
    public string $sortBy = 'name';

    public function render()
    {
        $query = Store::query()->where('status', 'approved');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->sortBy === 'products') {
            $query->withCount('products')->orderBy('products_count', 'desc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $stores = $query->get();

        return view('livewire.store-catalog', [
            'stores' => $stores
        ])->extends('layouts.app')->section('content');
    }
}
