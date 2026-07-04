<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class Categories extends Component
{
    public string $name = '';
    public ?int $categoryId = null;
    public string $search = '';
    public bool $showForm = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|min:3|max:50|unique:categories,name,' . $this->categoryId,
        ];
    }

    protected $messages = [
        'name.required' => 'Nama kategori wajib diisi.',
        'name.unique' => 'Nama kategori sudah digunakan.',
        'name.min' => 'Nama kategori minimal 3 karakter.',
    ];

    public function showCreateForm()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editCategory(int $id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->showForm = true;
    }

    public function cancel()
    {
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->categoryId = null;
        $this->name = '';
        $this->showForm = false;
        $this->resetValidation();
    }

    public function saveCategory()
    {
        $this->validate();

        $slug = Str::slug($this->name);

        if ($this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $category->update([
                'name' => $this->name,
                'slug' => $slug
            ]);
            session()->flash('success', 'Kategori berhasil diubah.');
        } else {
            Category::create([
                'name' => $this->name,
                'slug' => $slug
            ]);
            session()->flash('success', 'Kategori berhasil ditambahkan.');
        }

        $this->resetForm();
    }

    public function deleteCategory(int $id)
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            session()->flash('error', 'Kategori "' . $category->name . '" tidak dapat dihapus karena memiliki ' . $category->products_count . ' produk aktif.');
            return;
        }

        $category->delete();
        session()->flash('success', 'Kategori berhasil dihapus.');
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        $categories = Category::withCount('products')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.admin.categories', [
            'categories' => $categories
        ])->extends('layouts.dashboard')->section('content');
    }
}
