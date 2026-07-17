<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-semibold">
            {{ session('error') }}
        </div>
    @endif

    {{-- Hero Header --}}
    <div class="relative bg-white rounded-2xl shadow-card overflow-hidden mb-6">
        {{-- Accent bar --}}
        <div class="absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-primary-400 to-primary-600 rounded-l-2xl"></div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-5 pl-7">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                <div class="w-12 h-12 rounded-xl bg-primary-500 flex items-center justify-center shrink-0">
                    <span class="text-lg font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                <div>
                    <p class="text-xs font-semibold text-surface-400 uppercase tracking-widest mb-0.5">Selamat datang kembali</p>
                    <h1 class="font-heading text-xl font-bold text-surface-900">{{ $user->name }}</h1>
                </div>
            </div>
            <a href="{{ url('/products') }}" wire:navigate
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-all text-sm shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                Belanja Sekarang
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Pesanan Aktif"          value="{{ $pesananAktif }}"                                     icon="cart"  color="blue"   />
        <x-stat-card label="Pesanan Selesai"         value="{{ $pesananSelesai }}"                                   icon="chart" color="green"  />
        <x-stat-card label="Total Belanja"           value="Rp {{ number_format($totalBelanja, 0, ',', '.') }}"     icon="money" color="accent" />
        <x-stat-card label="Menunggu Pembayaran"     value="{{ $menungguPembayaran }}"                               icon="star"  color="purple" />
    </div>

    {{-- Main Grid --}}
    <div class="grid lg:grid-cols-3 gap-6">

        {{-- Pesanan Terbaru --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-card overflow-hidden">
            <div class="px-5 py-4 border-b border-surface-100 flex items-center justify-between">
                <div>
                    <h2 class="font-heading font-bold text-surface-900">Pesanan Terbaru</h2>
                    <p class="text-xs text-surface-400 mt-0.5">Riwayat 5 pesanan terakhir Anda</p>
                </div>
                <a href="{{ url('/customer/orders') }}" wire:navigate
                   class="text-xs font-semibold text-primary-500 hover:text-primary-600 transition-colors">
                    Lihat Semua →
                </a>
            </div>

            @forelse($recentOrders as $order)
                <div class="flex items-center gap-4 px-5 py-4 border-b border-surface-50 last:border-0 hover:bg-surface-50/50 transition-colors">
                    {{-- Status dot --}}
                    @php
                        $dot = match($order->status) {
                            'waiting_payment' => 'bg-yellow-400',
                            'processing'      => 'bg-blue-400',
                            'shipped'         => 'bg-purple-400',
                            'delivered'       => 'bg-green-500',
                            'cancelled'       => 'bg-red-400',
                            default           => 'bg-surface-300',
                        };
                    @endphp
                    <span class="shrink-0 w-2.5 h-2.5 rounded-full {{ $dot }}"></span>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-surface-800">{{ $order->order_code }}</span>
                            <span class="text-xs text-surface-400">·</span>
                            <span class="text-xs text-surface-500 truncate">{{ $order->store->name }}</span>
                        </div>
                        <p class="text-xs text-surface-400 mt-0.5">{{ $order->created_at->format('d M Y') }}</p>
                    </div>

                    {{-- Total + Status + Link --}}
                    <div class="flex items-center gap-4 shrink-0">
                        <span class="text-sm font-bold text-surface-800 hidden sm:block">
                            Rp {{ number_format($order->total_price, 0, ',', '.') }}
                        </span>
                        <x-status-badge :status="$order->status" />
                        <a href="{{ url('/customer/orders/' . $order->id) }}" wire:navigate
                           class="text-xs font-semibold text-primary-500 hover:text-primary-600 transition-colors">
                            Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-14 text-surface-400">
                    <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p class="text-sm font-medium">Belum ada pesanan</p>
                    <a href="{{ url('/products') }}" wire:navigate class="mt-3 text-xs text-primary-500 hover:underline font-semibold">
                        Mulai belanja sekarang
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Alamat Pengiriman --}}
            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-surface-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <h3 class="font-heading font-bold text-surface-900 text-sm">Alamat Pengiriman</h3>
                </div>

                <div class="p-5">
                    @if (session()->has('success'))
                        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-xs font-medium">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session()->has('shipping-sync-warning'))
                        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-xs font-medium leading-relaxed">
                            {{ session('shipping-sync-warning') }}
                        </div>
                    @endif

                    @if(!$user->address)
                        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <p class="text-xs text-amber-700 font-medium leading-relaxed">
                                Alamat pengiriman belum diatur. Lengkapi sebelum melakukan pembelian.
                            </p>
                        </div>
                    @endif

                    @if($isEditingAddress)
                        <form wire:submit.prevent="updateAddress" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1.5">Kecamatan</label>
                                <select wire:model.live="selectedDistrictCode"
                                        class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 bg-white">
                                    @foreach($this->districts as $d)
                                        <option value="{{ $d['code'] }}">{{ $d['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('selectedDistrictCode') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1.5">Desa / Kelurahan</label>
                                <select wire:model.live="selectedVillageCode"
                                        class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 bg-white">
                                    <option value="">Pilih Desa/Kelurahan</option>
                                    @foreach($this->villages as $v)
                                        <option value="{{ $v['code'] }}">{{ $v['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('selectedVillageCode') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1.5">Detail Alamat</label>
                                <textarea wire:model="detailAddress" rows="3"
                                          placeholder="Nama jalan, no. rumah, RT/RW..."
                                          class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 resize-none"></textarea>
                                @error('detailAddress') <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="p-3 bg-surface-50 rounded-xl text-xs text-surface-500">
                                Kabupaten: <strong class="text-surface-700">Lampung Barat</strong>
                            </div>

                            @php($shippingPreview = $this->shippingPreview)
                            @if(count($shippingPreview['orders']) > 0)
                                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl space-y-2">
                                    <p class="text-xs font-bold text-blue-800">Dampak ke pesanan aktif</p>
                                    @foreach($shippingPreview['orders'] as $preview)
                                        <div class="p-2.5 bg-white/80 border border-blue-100 rounded-lg text-xs">
                                            <div class="flex justify-between gap-3">
                                                <span class="font-semibold text-surface-700">#{{ $preview['order_code'] }}</span>
                                                <span class="text-surface-500 truncate">{{ $preview['store_name'] }}</span>
                                            </div>
                                            <div class="flex justify-between gap-3 mt-1 text-surface-600">
                                                <span>Ongkir</span>
                                                <span>Rp {{ number_format($preview['old_cost'], 0, ',', '.') }} → <strong class="text-blue-700">Rp {{ number_format($preview['new_cost'], 0, ',', '.') }}</strong></span>
                                            </div>
                                            <div class="flex justify-between gap-3 mt-1 text-surface-600">
                                                <span>Total baru</span>
                                                <strong class="text-blue-700">Rp {{ number_format($preview['new_total'], 0, ',', '.') }}</strong>
                                            </div>
                                            <p class="mt-1 text-[11px] text-surface-500">{{ $preview['zone_label'] }}</p>
                                            @if($preview['invoice_will_reset'])
                                                <p class="mt-1 text-[11px] font-semibold text-amber-700">Invoice pembayaran lama akan dibatalkan karena total berubah.</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($shippingPreview['skipped'] > 0)
                                <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl text-[11px] text-amber-700 leading-relaxed">
                                    {{ $shippingPreview['skipped'] }} pesanan yang sudah lunas atau dikirim tidak akan diubah dan tetap memakai alamat sebelumnya.
                                </div>
                            @endif

                            <div class="flex gap-2 pt-1">
                                <button type="submit"
                                        class="flex-1 py-2.5 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-colors text-xs">
                                    Simpan
                                </button>
                                <button type="button" wire:click="cancelEdit"
                                        class="py-2.5 px-4 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition-colors text-xs">
                                    Batal
                                </button>
                            </div>
                        </form>
                    @else
                        @if($user->address)
                            <div class="p-3 bg-surface-50 rounded-xl mb-3">
                                <p class="text-sm text-surface-700 leading-relaxed">{{ $user->address }}</p>
                            </div>
                        @else
                            <div class="p-3 bg-surface-50 rounded-xl mb-3 text-center">
                                <p class="text-xs text-surface-400 italic">Belum ada alamat</p>
                            </div>
                        @endif

                        <button wire:click="editAddress"
                                class="w-full py-2.5 px-4 border border-surface-200 text-surface-700 font-semibold rounded-xl hover:bg-surface-50 hover:border-primary-300 transition-all text-xs flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ $user->address ? 'Ubah Alamat' : 'Tambah Alamat' }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- Info Akun --}}
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h3 class="font-heading font-bold text-surface-900 text-sm mb-3">Informasi Akun</h3>
                <div class="space-y-2.5">
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg bg-surface-100 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-surface-400 uppercase tracking-wider font-semibold">Nama</p>
                            <p class="text-sm font-medium text-surface-800 truncate">{{ $user->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg bg-surface-100 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-surface-400 uppercase tracking-wider font-semibold">Email</p>
                            <p class="text-sm font-medium text-surface-800 truncate">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-lg bg-surface-100 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] text-surface-400 uppercase tracking-wider font-semibold">Bergabung</p>
                            <p class="text-sm font-medium text-surface-800">{{ $user->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
