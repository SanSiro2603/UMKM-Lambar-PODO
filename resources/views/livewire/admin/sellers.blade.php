<div>
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    {{-- LIST VIEW --}}
    @if($view === 'list')
        {{-- Status Filter Tabs --}}
        <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
            @foreach(['semua' => 'Semua', 'pending' => 'Menunggu Verifikasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $key => $label)
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
                                    {{ $st->bank_name }} — {{ $st->bank_account_number }}
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
                                <p><span class="text-surface-500">Nomor Rekening:</span> <span class="font-medium text-surface-800">{{ $store->bank_account_number ?? 'Belum diisi' }}</span></p>
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
            </div>
        </div>
    @endif
</div>
