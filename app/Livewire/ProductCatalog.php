<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use App\Models\Product;
use App\Models\Category;

#[Layout('layouts.app')]
class ProductCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $searchQuery = '';

    #[Url(as: 'cat')]
    public string $categoryFilter = 'Semua';

    #[Url]
    public string $minPrice = '';

    #[Url]
    public string $maxPrice = '';



    #[Url]
    public string $sort = 'terbaru';

    public bool $filterOpen = false;

    // Reset pagination when filter properties change
    public function updatedSearchQuery() { $this->resetPage(); }
    public function updatedCategoryFilter() { $this->resetPage(); }
    public function updatedMinPrice() { $this->resetPage(); }
    public function updatedMaxPrice() { $this->resetPage(); }

    public function updatedSort() { $this->resetPage(); }

    public function resetFilters()
    {
        $this->searchQuery = '';
        $this->categoryFilter = 'Semua';
        $this->minPrice = '';
        $this->maxPrice = '';

        $this->sort = 'terbaru';
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::with(['category', 'store'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->withSoldQuantity()
            ->whereHas('store', function($q) {
            $q->where('status', 'approved');
        });

        // Search Query
        if ($this->searchQuery) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('description', 'like', '%' . $this->searchQuery . '%')
                  ->orWhereHas('store', function($sq) {
                      $sq->where('name', 'like', '%' . $this->searchQuery . '%');
                  });
            });
        }

        // Category Filter
        if ($this->categoryFilter && $this->categoryFilter !== 'Semua') {
            $query->whereHas('category', function($q) {
                $q->where('name', $this->categoryFilter);
            });
        }

        // Min Price
        if ($this->minPrice) {
            $query->where('price', '>=', (int) $this->minPrice);
        }

        // Max Price
        if ($this->maxPrice) {
            $query->where('price', '<=', (int) $this->maxPrice);
        }



        // Sorting
        if ($this->sort === 'termurah') {
            $query->orderBy('price', 'asc');
        } elseif ($this->sort === 'termahal') {
            $query->orderBy('price', 'desc');
        } elseif ($this->sort === 'terlaris') {
            $query->orderByDesc('sold_quantity');

        } else {
            $query->orderBy('id', 'desc');
        }

        $products = $query->paginate(12);
        $categoriesList = Category::all();

        return view('livewire.product-catalog', [
            'products' => $products,
            'categoriesList' => $categoriesList
        ]);
    }
}
