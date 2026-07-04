<div>
    @if($store->status !== 'approved')
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center shadow-card max-w-2xl mx-auto my-12">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-surface-900 mb-2">Toko Anda Sedang Ditinjau</h3>
            <p class="text-surface-600 text-sm mb-4">Toko Anda harus disetujui oleh admin terlebih dahulu sebelum Anda dapat mengelola produk.</p>
        </div>
    @else
        @if (session()->has('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{-- LIST VIEW --}}
        @if($view === 'list')
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="relative flex-1 max-w-md">
                    <input type="text" placeholder="Cari nama produk..." wire:model.live="search"
                           class="w-full pl-10 pr-4 py-2.5 bg-white rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button wire:click="showCreateForm" class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-all text-sm whitespace-nowrap shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Produk Baru
                </button>
            </div>

            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-surface-50">
                            <tr>
                                <th class="px-5 py-3 font-semibold text-surface-600">Produk</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Kategori</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Harga</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Stok</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Terjual</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-50">
                            @forelse($products as $product)
                                <tr class="hover:bg-surface-50/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center shrink-0 overflow-hidden">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg class="w-6 h-6 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                                @endif
                                            </div>
                                            <span class="font-medium text-surface-800 line-clamp-2">{{ $product->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-surface-600">{{ $product->category->name ?? 'Lainnya' }}</td>
                                    <td class="px-5 py-4 font-semibold text-surface-800">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4">
                                        <span class="{{ $product->stock > 0 ? 'text-green-600 font-medium' : 'text-red-500 font-bold' }}">
                                            {{ $product->stock > 0 ? $product->stock : 'Habis' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-surface-600">{{ $product->sold_quantity }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <button wire:click="showEditForm({{ $product->id }})" class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-500 transition-colors" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="deleteProduct({{ $product->id }})" wire:confirm="Apakah Anda yakin ingin menghapus produk ini?" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 transition-colors" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-surface-500 font-medium">
                                        Belum ada produk terdaftar. Silakan tambah produk baru.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-surface-100">
                    {{ $products->links() }}
                </div>
            </div>

        {{-- FORM VIEW --}}
        @else
            <div class="max-w-3xl">
                <button wire:click="cancel" class="inline-flex items-center gap-1 text-sm text-surface-500 hover:text-primary-500 mb-4 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Daftar Produk
                </button>

                <form wire:submit.prevent="saveProduct" class="space-y-6">
                    {{-- Basic Info --}}
                    <div class="bg-white rounded-2xl shadow-card p-5 space-y-4">
                        <h3 class="font-heading font-bold text-surface-900">
                            {{ $view === 'create' ? 'Tambah Produk Baru' : 'Edit Produk' }}
                        </h3>

                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1.5">Nama Produk</label>
                            <input type="text" wire:model="name" placeholder="Contoh: Kopi Robusta Premium 250gr"
                                   class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                            @error('name') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1.5">Deskripsi</label>
                            <textarea rows="4" wire:model="description" placeholder="Jelaskan detail produk Anda..."
                                      class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all resize-none"></textarea>
                            @error('description') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-surface-700 mb-1.5">Kategori</label>
                            <select wire:model="category_id" class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100">
                                <option value="">Pilih kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1.5">Harga (Rp)</label>
                                <input type="number" wire:model="price" placeholder="75000"
                                       class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                                @error('price') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-surface-700 mb-1.5">Stok</label>
                                <input type="number" wire:model="stock" placeholder="100"
                                       class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                                @error('stock') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Photos --}}
                    <div class="bg-white rounded-2xl shadow-card p-5">
                        <h3 class="font-heading font-bold text-surface-900 mb-4">Foto Produk</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            {{-- Upload input --}}
                            <label class="aspect-square rounded-xl border-2 border-dashed border-surface-300 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-primary-400 hover:bg-primary-50/50 transition-all relative overflow-hidden">
                                @if($image)
                                    <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                @elseif($existingImage)
                                    <img src="{{ asset('storage/' . $existingImage) }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-8 h-8 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span class="text-xs text-surface-500 font-medium">Ubah Foto</span>
                                @endif
                                <input type="file" wire:model="image" accept="image/*" class="hidden">
                            </label>
                        </div>
                        @error('image') <span class="text-xs text-red-600 block mt-2">{{ $message }}</span> @enderror
                        <p class="text-xs text-surface-400 mt-3">Format: JPG, PNG, JPEG. Maksimal 2MB. Disarankan rasio 1:1.</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 py-3 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg text-sm">
                            <span wire:loading.remove wire:target="saveProduct">Simpan Produk</span>
                            <span wire:loading wire:target="saveProduct" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span wire:loading wire:target="saveProduct">Menyimpan...</span>
                        </button>
                        <button type="button" wire:click="cancel" class="px-6 py-3 bg-white border border-surface-300 text-surface-600 font-medium rounded-xl hover:bg-surface-50 transition-colors text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif
</div>
