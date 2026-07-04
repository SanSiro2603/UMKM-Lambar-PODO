<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="font-heading text-2xl font-bold text-surface-900">Halo, {{ $user->name }}! 👋</h1>
            <p class="text-surface-500 mt-1">Selamat datang kembali di dashboard Anda</p>
        </div>
        <a href="{{ url('/products') }}" wire:navigate class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-all text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Belanja Lagi
        </a>
    </div>

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-semibold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-stat-card label="Pesanan Aktif" value="{{ $pesananAktif }}" icon="cart" color="blue" />
        <x-stat-card label="Pesanan Selesai" value="{{ $pesananSelesai }}" icon="chart" color="green" />
        <x-stat-card label="Total Belanja" value="Rp {{ number_format($totalBelanja, 0, ',', '.') }}" icon="money" color="accent" />
        <x-stat-card label="Menunggu Pembayaran" value="{{ $menungguPembayaran }}" icon="star" color="purple" />
    </div>

    {{-- Main Grid --}}
    <div class="grid lg:grid-cols-3 gap-8">
        {{-- Recent Orders --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-card overflow-hidden h-fit">
            <div class="px-5 py-4 border-b border-surface-100 flex items-center justify-between">
                <h2 class="font-heading font-bold text-surface-900">Pesanan Terbaru</h2>
                <a href="{{ url('/customer/orders') }}" wire:navigate class="text-sm font-medium text-primary-500 hover:text-primary-600 transition-colors">Lihat Semua</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface-50 text-left">
                        <tr>
                            <th class="px-5 py-3 font-semibold text-surface-600">ID Pesanan</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Toko</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Total</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Tanggal</th>
                            <th class="px-5 py-3 font-semibold text-surface-600">Status</th>
                            <th class="px-5 py-3 font-semibold text-surface-600"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-50">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-surface-50/50 transition-colors">
                                <td class="px-5 py-4 font-medium text-surface-800">#{{ $order->order_code }}</td>
                                <td class="px-5 py-4 text-surface-600">{{ $order->store->name }}</td>
                                <td class="px-5 py-4 font-semibold text-surface-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-surface-500">{{ $order->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-4"><x-status-badge :status="$order->status" /></td>
                                <td class="px-5 py-4"><a href="{{ url('/customer/orders/' . $order->id) }}" wire:navigate class="text-primary-500 hover:underline font-medium">Detail</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-surface-500 font-medium">
                                    Belum ada pesanan terbaru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Address Profile Card --}}
        <div class="lg:col-span-1 bg-white rounded-2xl shadow-card p-5 h-fit">
            <h3 class="font-heading font-bold text-surface-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Alamat Pengiriman Saya
            </h3>

            @if (session()->has('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-600 rounded-xl text-xs font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if(!$user->address)
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-xs leading-relaxed font-semibold">
                    ⚠️ Alamat pengiriman belum diatur! Anda wajib melengkapi alamat pengiriman sebelum melakukan pembelian.
                </div>
            @endif

            @if($isEditingAddress)
                <form wire:submit.prevent="updateAddress" class="space-y-4">
                    {{-- Kecamatan Selector --}}
                    <div>
                        <label for="selectedDistrictCode" class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1">Kecamatan (Kab. Lampung Barat)</label>
                        <select id="selectedDistrictCode" wire:model.change="selectedDistrictCode" class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 bg-white">
                            @foreach($this->districts as $d)
                                <option value="{{ $d['code'] }}">{{ $d['name'] }}</option>
                            @endforeach
                        </select>
                        @error('selectedDistrictCode') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Kelurahan / Desa Selector --}}
                    <div>
                        <label for="selectedVillageCode" class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1">Kelurahan / Desa / Pekon</label>
                        <select id="selectedVillageCode" wire:model.change="selectedVillageCode" class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 bg-white">
                            <option value="">Pilih Desa/Kelurahan</option>
                            @foreach($this->villages as $v)
                                <option value="{{ $v['code'] }}">{{ $v['name'] }}</option>
                            @endforeach
                        </select>
                        @error('selectedVillageCode') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Detail Address --}}
                    <div>
                        <label for="detailAddress" class="block text-xs font-semibold text-surface-500 uppercase tracking-wider mb-1">Nama Jalan / No. Rumah / RT / RW</label>
                        <textarea id="detailAddress" wire:model="detailAddress" rows="3" placeholder="Contoh: Gang Cempaka No. 12, RT 03/RW 02, dekat mushola" class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 resize-none"></textarea>
                        @error('detailAddress') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Hardcoded Region --}}
                    <div class="p-3 bg-surface-50 border border-surface-200 rounded-xl text-xs text-surface-500 space-y-1">
                        <p class="font-semibold text-surface-700">Wilayah Terkunci:</p>
                        <p>Kabupaten: <strong>Lampung Barat</strong></p>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2 px-3 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-colors text-xs">
                            Simpan Alamat
                        </button>
                        <button type="button" wire:click="cancelEdit" class="py-2 px-3 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition-colors text-xs">
                            Batal
                        </button>
                    </div>
                </form>
            @else
                <div class="p-4 bg-surface-50 border border-surface-200 rounded-xl space-y-3">
                    <p class="text-sm text-surface-700 leading-relaxed font-medium">
                        @if($user->address)
                            {{ $user->address }}
                        @else
                            <span class="text-surface-400 italic">Alamat belum diisi. Silakan klik tombol di bawah untuk menambah alamat.</span>
                        @endif
                    </p>

                    <button wire:click="editAddress" class="w-full py-2.5 px-4 bg-white border-2 border-primary-500 text-primary-500 font-semibold rounded-xl hover:bg-primary-50 transition-colors text-xs flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        {{ $user->address ? 'Ubah Alamat' : 'Set Alamat Sekarang' }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
