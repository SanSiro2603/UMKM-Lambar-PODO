<div>
    @if (session()->has('success'))
        <div role="status" class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div role="alert" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    {{-- LIST VIEW --}}
    @if($view === 'list')
        <div class="relative mb-4 max-w-xl">
            <label for="seller-search" class="sr-only">Cari toko atau pemilik</label>
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="seller-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Cari toko atau pemilik"
                   class="w-full min-h-11 pl-10 pr-4 py-2.5 rounded-xl border border-surface-300 bg-white text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100">
        </div>

        {{-- Status Filter Tabs --}}
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            @foreach(['semua' => 'Semua', 'pending' => 'Menunggu Verifikasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'suspended' => 'Dinonaktifkan'] as $key => $label)
                <button wire:click="filterSellers('{{ $key }}')"
                        class="px-4 py-2 rounded-xl text-sm font-medium border whitespace-nowrap transition-all
                               {{ $statusFilter === $key ? 'bg-primary-500 text-white border-primary-500' : 'bg-white text-surface-600 hover:bg-surface-50 border-surface-200' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl shadow-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50">
                        <tr>
                            <th class="px-5 py-3 font-semibold text-surface-600">Nama Toko</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Pemilik</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">No. Rekening</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Tanggal Daftar</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Status</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-50">
                        @forelse($stores as $st)
                            <tr class="hover:bg-surface-50/50 transition-colors">
                                <td class="px-5 py-4 font-semibold text-surface-800">{{ $st->name }}</td>
                                <td class="px-5 py-4 text-surface-600">{{ $st->user->name }}</td>
                                <td class="px-5 py-4 text-surface-600">
                                    {{ $st->bank_name ?? '-' }} — {{ $st->bank_account_no ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-surface-500">{{ $st->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <x-status-badge :status="$st->status" />
                                </td>
                                <td class="px-5 py-4">
                                    <button wire:click="showStore({{ $st->id }})" class="text-sm text-primary-500 hover:underline font-semibold">
                                        Tinjau
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-surface-500 font-medium">
                                    Belum ada data toko terdaftar dalam kategori ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    {{-- SHOW VIEW --}}
    @else
        <button wire:click="backToList" class="inline-flex items-center gap-1 text-sm text-surface-500 hover:text-primary-500 mb-6 transition-colors font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Toko
        </button>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Store Identity --}}
                <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                    {{-- Banner preview in admin review --}}
                    <div class="h-32 relative overflow-hidden bg-surface-100">
                        @if($store->banner)
                            <img src="{{ asset('storage/' . $store->banner) }}" alt="Banner" class="w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 hero-gradient"></div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-r from-primary-700/40 to-transparent"></div>
                    </div>

                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-4 border-b border-surface-100 pb-3 relative">
                            {{-- Logo preview --}}
                            <div class="w-16 h-16 rounded-xl bg-white shadow-card flex items-center justify-center border border-surface-200 shrink-0 overflow-hidden">
                                @if($store->logo)
                                    <img src="{{ asset('storage/' . $store->logo) }}" alt="Logo" class="w-full h-full object-cover">
                                @else
                                    <span class="font-heading font-bold text-xl text-primary-500">{{ strtoupper(substr($store->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <h3 class="font-heading font-bold text-lg text-surface-900">{{ $store->name }}</h3>
                                <p class="text-xs text-surface-400 mt-0.5">Slug: /stores/{{ $store->slug }}</p>
                            </div>
                            <x-status-badge :status="$store->status" />
                        </div>
                        <div class="space-y-4 text-sm">
                            <div>
                                <p class="text-surface-500 font-medium">Deskripsi Toko</p>
                                <p class="text-surface-700 mt-1 leading-relaxed bg-surface-50 p-3 rounded-xl border border-surface-100">{{ $store->description ?? 'Tidak ada deskripsi.' }}</p>
                            </div>
                            <div>
                                <p class="text-surface-500 font-medium">Alamat Toko</p>
                                <p class="text-surface-700 mt-1">{{ $store->address ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Owner Info --}}
                <div class="bg-white rounded-2xl shadow-card p-5">
                    <h3 class="font-heading font-bold text-surface-900 mb-3">Info Pemilik</h3>
                    <div class="space-y-2 text-sm">
                        <p><span class="text-surface-500">Nama:</span> <span class="font-medium text-surface-800">{{ $store->user->name }}</span></p>
                        <p><span class="text-surface-500">Email:</span> <span class="font-medium text-surface-800">{{ $store->user->email }}</span></p>
                        <p><span class="text-surface-500">Telepon:</span> <span class="font-medium text-surface-800">{{ $store->user->phone }}</span></p>
                    </div>
                </div>

                {{-- Bank Info --}}
                <div class="bg-white rounded-2xl shadow-card p-5">
                    <h3 class="font-heading font-bold text-surface-900 mb-3">Informasi Rekening & Pembayaran</h3>
                    <div class="space-y-4 text-sm">
                        @if($store->paymentMethods->isEmpty())
                            <div class="space-y-2">
                                <p><span class="text-surface-500">Nama Bank:</span> <span class="font-medium text-surface-800">{{ $store->bank_name ?? 'Belum diisi' }}</span></p>
                                <p><span class="text-surface-500">Nomor Rekening:</span> <span class="font-medium text-surface-800">{{ $store->bank_account_no ?? 'Belum diisi' }}</span></p>
                                <p><span class="text-surface-500">Atas Nama:</span> <span class="font-medium text-surface-800">{{ $store->bank_account_name ?? 'Belum diisi' }}</span></p>
                            </div>
                        @else
                            @foreach($store->paymentMethods as $m)
                                <div class="p-3 bg-surface-50 border border-surface-200 rounded-xl space-y-1">
                                    <p class="font-semibold text-surface-800">
                                        {{ $m->name }} 
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full {{ $m->type === 'bank' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600' }}">
                                            {{ strtoupper($m->type) }}
                                        </span>
                                    </p>
                                    <p class="text-xs text-surface-600"><span class="text-surface-500">No. Akun:</span> {{ $m->account_number }}</p>
                                    <p class="text-xs text-surface-600"><span class="text-surface-500">Atas Nama:</span> {{ $m->account_name }}</p>
                                    @if($m->qr_code)
                                        <div class="mt-2 text-center">
                                            <a href="{{ asset('storage/' . $m->qr_code) }}" target="_blank" class="text-xs text-primary-500 font-bold hover:underline">Lihat Barcode QR</a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                @if($store->status === 'pending')
                    <div class="bg-white rounded-2xl shadow-card p-5">
                        <h3 class="font-heading font-bold text-surface-900 mb-4">Verifikasi Toko</h3>
                        <p class="text-xs text-surface-500 mb-4">Tinjau kesesuaian data pemilik sebelum menyetujui pendaftaran toko.</p>
                        <div class="space-y-2">
                            <button wire:click="approveStore" class="w-full py-2.5 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 transition-all text-sm flex items-center justify-center gap-1.5 shadow-md">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Setujui Toko
                            </button>
                            <button wire:click="rejectStore" wire:confirm="Apakah Anda yakin ingin menolak toko ini?" class="w-full py-2.5 bg-white border border-red-500 text-red-500 font-bold rounded-xl hover:bg-red-50 transition-colors text-sm">
                                Tolak Toko
                            </button>
                        </div>
                    </div>
                @endif

                @if($store->status === 'approved')
                    <div class="bg-white rounded-2xl shadow-card p-5 border border-amber-200">
                        <h3 class="font-heading font-bold text-surface-900 mb-2">Moderasi Seller</h3>
                        <p class="text-xs text-surface-600 mb-4">Nonaktifkan seller untuk menyembunyikan toko dan memblokir akses login tanpa menghapus data.</p>
                        <button type="button" wire:click="openSuspendModal" wire:loading.attr="disabled" wire:target="openSuspendModal"
                                class="w-full min-h-11 px-4 py-2.5 bg-amber-500 text-white font-bold rounded-xl hover:bg-amber-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition-colors motion-reduce:transition-none text-sm cursor-pointer">
                            <span wire:loading.remove wire:target="openSuspendModal">Nonaktifkan Seller</span>
                            <span wire:loading wire:target="openSuspendModal">Membuka...</span>
                        </button>
                    </div>
                @elseif($store->status === 'suspended')
                    <div class="bg-white rounded-2xl shadow-card p-5 border border-slate-300">
                        <h3 class="font-heading font-bold text-surface-900 mb-2">Seller Dinonaktifkan</h3>
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 mb-4 text-xs text-slate-700 space-y-1">
                            <p class="font-semibold">Alasan:</p>
                            <p class="leading-relaxed">{{ $store->suspension_reason }}</p>
                            @if($store->suspended_at)
                                <p class="pt-1 text-slate-500">{{ $store->suspended_at->format('d M Y, H:i') }}{{ $store->suspendedBy ? ' oleh '.$store->suspendedBy->name : '' }}</p>
                            @endif
                        </div>
                        <button type="button" wire:click="openReactivateModal"
                                wire:loading.attr="disabled" wire:target="openReactivateModal"
                                class="w-full min-h-11 px-4 py-2.5 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-400 focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition-colors motion-reduce:transition-none text-sm cursor-pointer">
                            <span wire:loading.remove wire:target="openReactivateModal">Aktifkan Kembali</span>
                            <span wire:loading wire:target="openReactivateModal">Membuka...</span>
                        </button>
                    </div>
                @endif

                <div class="bg-red-50 rounded-2xl border border-red-200 p-5">
                    <h3 class="font-heading font-bold text-red-800 mb-2">Zona Berbahaya</h3>
                    <p class="text-xs text-red-700 mb-4">Penghapusan bersifat permanen dan menghilangkan akun, produk, pesanan, rating, serta transaksi seller.</p>
                    <button type="button" wire:click="openDeleteModal" wire:loading.attr="disabled" wire:target="openDeleteModal"
                            class="w-full min-h-11 px-4 py-2.5 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition-colors motion-reduce:transition-none text-sm cursor-pointer">
                        <span wire:loading.remove wire:target="openDeleteModal">Hapus Permanen</span>
                        <span wire:loading wire:target="openDeleteModal">Membuka...</span>
                    </button>
                </div>
            </div>
        </div>

        @if($showSuspendModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="suspend-dialog-title"
                 x-data x-init="$nextTick(() => $refs.suspensionReason.focus())" @keydown.escape.window="$wire.closeSuspendModal()">
                <button type="button" aria-label="Tutup dialog penonaktifan" wire:click="closeSuspendModal" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm cursor-default"></button>
                <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-surface-200 p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="w-11 h-11 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728m0-12.728l12.728 12.728"/></svg>
                        </span>
                        <div>
                            <h2 id="suspend-dialog-title" class="font-heading text-lg font-bold text-surface-900">Nonaktifkan {{ $store->name }}?</h2>
                            <p class="text-sm text-surface-600 mt-1">Toko disembunyikan dan seller langsung kehilangan akses login. Seluruh data tetap tersimpan.</p>
                        </div>
                    </div>

                    <form wire:submit="suspendStore" class="mt-5 space-y-4">
                        <div>
                            <label for="suspension-reason" class="block text-sm font-semibold text-surface-800 mb-1.5">Alasan penonaktifan</label>
                            <textarea id="suspension-reason" x-ref="suspensionReason" wire:model="suspensionReason" rows="4" maxlength="500"
                                      placeholder="Contoh: Produk yang dijual tidak sesuai dengan ketentuan marketplace."
                                      class="w-full rounded-xl border border-surface-300 px-4 py-3 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                                      aria-describedby="suspension-help suspension-error"></textarea>
                            <div class="mt-1 flex justify-between gap-3 text-xs text-surface-500">
                                <span id="suspension-help">Minimal 5, maksimal 500 karakter.</span>
                                <span>{{ mb_strlen($suspensionReason) }}/500</span>
                            </div>
                            @error('suspensionReason') <p id="suspension-error" role="alert" class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                            <button type="button" wire:click="closeSuspendModal" class="min-h-11 px-4 py-2.5 rounded-xl border border-surface-300 text-sm font-semibold text-surface-700 hover:bg-surface-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 cursor-pointer">Batal</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="suspendStore" class="min-h-11 px-4 py-2.5 rounded-xl bg-amber-500 text-white text-sm font-bold hover:bg-amber-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
                                <span wire:loading.remove wire:target="suspendStore">Ya, Nonaktifkan</span>
                                <span wire:loading wire:target="suspendStore">Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        @if($showReactivateModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="reactivate-dialog-title"
                 x-data x-init="$nextTick(() => $refs.cancelReactivate.focus())" @keydown.escape.window="$wire.closeReactivateModal()">
                <button type="button" aria-label="Tutup dialog aktivasi seller" wire:click="closeReactivateModal" class="absolute inset-0 bg-slate-950/60 cursor-default"></button>
                <div class="relative w-full max-w-md rounded-2xl bg-white p-5 shadow-modal sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-100 text-green-700">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <h2 id="reactivate-dialog-title" class="font-heading text-lg font-bold text-surface-900">Aktifkan kembali {{ $store->name }}?</h2>
                            <p class="mt-1 text-sm leading-relaxed text-surface-600">Seller dapat login kembali dan tokonya akan tampil lagi di marketplace.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" x-ref="cancelReactivate" wire:click="closeReactivateModal"
                                class="min-h-11 rounded-xl border border-surface-300 px-4 py-2.5 text-sm font-semibold text-surface-700 transition-colors hover:bg-surface-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 cursor-pointer motion-reduce:transition-none">
                            Batal
                        </button>
                        <button type="button" wire:click="reactivateStore" wire:loading.attr="disabled" wire:target="reactivateStore"
                                class="min-h-11 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-bold text-white transition-colors hover:bg-green-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 cursor-pointer motion-reduce:transition-none">
                            <span wire:loading.remove wire:target="reactivateStore">Aktifkan Seller</span>
                            <span wire:loading wire:target="reactivateStore">Mengaktifkan...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        @if($showDeleteModal)
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="delete-dialog-title"
                 x-data x-init="$nextTick(() => $refs.deleteReason.focus())" @keydown.escape.window="$wire.closeDeleteModal()">
                <button type="button" aria-label="Tutup dialog penghapusan" wire:click="closeDeleteModal" class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm cursor-default"></button>
                <div class="relative w-full max-w-lg max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-red-200 p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="w-11 h-11 rounded-xl bg-red-100 text-red-700 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.667 1.73-3L13.73 4c-.77-1.333-2.69-1.333-3.46 0L3.34 16c-.77 1.333.19 3 1.73 3z"/></svg>
                        </span>
                        <div>
                            <h2 id="delete-dialog-title" class="font-heading text-lg font-bold text-red-800">Hapus {{ $store->name }} permanen?</h2>
                            <p class="text-sm text-red-700 mt-1">Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl bg-red-50 border border-red-200 p-3 text-sm text-red-800">
                        <p class="font-semibold">Data yang akan dihapus:</p>
                        <p class="mt-1">{{ $store->products_count }} produk, {{ $store->orders_count }} pesanan, akun seller, rating, metode pembayaran, dan transaksi terkait.</p>
                    </div>

                    <form wire:submit="deleteStorePermanently" class="mt-5 space-y-4">
                        <div>
                            <label for="delete-reason" class="block text-sm font-semibold text-surface-800 mb-1.5">Alasan penghapusan</label>
                            <textarea id="delete-reason" x-ref="deleteReason" wire:model="deleteReason" rows="3" maxlength="500"
                                      class="w-full rounded-xl border border-surface-300 px-4 py-3 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100"
                                      aria-describedby="delete-reason-help delete-reason-error"></textarea>
                            <p id="delete-reason-help" class="mt-1 text-xs text-surface-500">Minimal 5, maksimal 500 karakter.</p>
                            @error('deleteReason') <p id="delete-reason-error" role="alert" class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="delete-confirmation" class="block text-sm font-semibold text-surface-800 mb-1.5">Ketik <span class="text-red-700">{{ $store->name }}</span> untuk mengonfirmasi</label>
                            <input id="delete-confirmation" type="text" wire:model="deleteConfirmation" autocomplete="off"
                                   class="w-full rounded-xl border border-surface-300 px-4 py-3 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-100"
                                   aria-describedby="delete-confirmation-error">
                            @error('deleteConfirmation') <p id="delete-confirmation-error" role="alert" class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                            <button type="button" wire:click="closeDeleteModal" class="min-h-11 px-4 py-2.5 rounded-xl border border-surface-300 text-sm font-semibold text-surface-700 hover:bg-surface-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 cursor-pointer">Batal</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="deleteStorePermanently" class="min-h-11 px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
                                <span wire:loading.remove wire:target="deleteStorePermanently">Hapus Selamanya</span>
                                <span wire:loading wire:target="deleteStorePermanently">Menghapus...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
