<div>
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

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div class="relative flex-1 max-w-md">
            <input type="text" placeholder="Cari kategori..." wire:model.live="search"
                   class="w-full pl-10 pr-4 py-2.5 bg-white rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>

        <button wire:click="showCreateForm" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-all text-sm whitespace-nowrap shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
        </button>
    </div>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold text-surface-600">Nama Kategori</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Slug</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Jumlah Produk</th>
                        <th class="px-5 py-3 font-semibold text-surface-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-50">
                    @forelse($categories as $category)
                        <tr class="hover:bg-surface-50/50 transition-colors" wire:key="category-{{ $category->id }}">
                            <td class="px-5 py-4 font-medium text-surface-800">{{ $category->name }}</td>
                            <td class="px-5 py-4 text-surface-500 font-mono text-xs">{{ $category->slug }}</td>
                            <td class="px-5 py-4 text-surface-600 font-semibold">{{ $category->products_count }} produk</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <button wire:click="editCategory({{ $category->id }})" class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-500 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="deleteCategory({{ $category->id }})" wire:confirm="Apakah Anda yakin ingin menghapus kategori ini?" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-surface-500 font-medium">
                                Tidak ada kategori ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <button type="button" wire:click="cancel" class="absolute inset-0 bg-black/50" aria-label="Tutup modal kategori"></button>

            <div class="relative bg-white rounded-2xl shadow-modal p-6 max-w-md w-full">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <h3 class="font-heading text-lg font-bold text-surface-900">
                        {{ $categoryId ? 'Edit Kategori' : 'Tambah Kategori' }}
                    </h3>
                    <button type="button" wire:click="cancel" class="text-surface-400 hover:text-surface-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveCategory" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-surface-700 mb-1.5">Nama Kategori</label>
                        <input type="text" wire:model="name" placeholder="Contoh: Agrokimia"
                               class="w-full px-4 py-3 rounded-xl border border-surface-300 text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 transition-all">
                        @error('name')
                            <span class="text-xs text-red-600 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-colors text-sm">
                            {{ $categoryId ? 'Simpan Perubahan' : 'Simpan' }}
                        </button>
                        <button type="button" wire:click="cancel" class="px-4 py-2.5 bg-surface-100 text-surface-600 font-medium rounded-xl hover:bg-surface-200 transition-colors text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
