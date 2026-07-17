<div x-data @open-whatsapp.window="window.open($event.detail.url, '_blank')">
    @if($store->status !== 'approved')
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center shadow-card max-w-2xl mx-auto my-12">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-surface-900 mb-2">Toko Anda Sedang Ditinjau</h3>
            <p class="text-surface-600 text-sm mb-4">Toko Anda harus disetujui oleh admin sebelum dapat mengelola pesanan.</p>
        </div>
    @else
        @if(session()->has('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">{{ session('success') }}</div>
        @endif
        @if(session()->has('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">{{ session('error') }}</div>
        @endif

        {{-- LIST VIEW --}}
        @if($view === 'list')
            <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
                @foreach(['semua' => 'Semua', 'waiting_payment' => 'Menunggu Bayar', 'processing' => 'Siap Diproses', 'shipped' => 'Dikirim', 'delivered' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $key => $label)
                    <button wire:click="selectTab('{{ $key }}')" class="px-4 py-2 rounded-xl text-sm font-medium border whitespace-nowrap transition-all {{ $statusTab === $key ? 'bg-primary-500 text-white border-primary-500' : 'bg-white text-surface-600 hover:bg-surface-50 border-surface-200' }}">{{ $label }}</button>
                @endforeach
            </div>

            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-surface-50">
                            <tr>
                                <th class="px-5 py-3 font-semibold text-surface-600">ID</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Customer</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Total</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Pembayaran</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Status</th>
                                <th class="px-5 py-3 font-semibold text-surface-600">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-50">
                            @forelse($orders as $o)
                                <tr class="hover:bg-surface-50/50 transition-colors">
                                    <td class="px-5 py-4 font-medium text-surface-800">#{{ $o->order_code }}</td>
                                    <td class="px-5 py-4 text-surface-600"><p class="font-medium text-surface-800">{{ $o->customer->name }}</p><p class="text-xs text-surface-400">{{ $o->created_at->format('d M Y, H:i') }}</p></td>
                                    <td class="px-5 py-4 font-semibold text-surface-800">Rp {{ number_format($o->total_price,0,',','.') }}</td>
                                    <td class="px-5 py-4"><span class="text-xs font-medium px-2 py-1 rounded-full {{ $o->payment_method === 'cod' ? 'bg-orange-50 text-orange-600' : 'bg-blue-50 text-blue-600' }}">{{ $o->payment_method === 'cod' ? 'COD' : 'Bayar Online' }}</span></td>
                                    <td class="px-5 py-4"><x-status-badge :status="$o->status" /></td>
                                    <td class="px-5 py-4"><button wire:click="showOrder({{ $o->id }})" class="text-sm text-primary-500 hover:underline font-semibold">Kelola</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-8 text-center text-surface-500 font-medium">Belum ada pesanan masuk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        {{-- SHOW VIEW --}}
        @else
            <button wire:click="backToList" class="inline-flex items-center gap-1 text-sm text-surface-500 hover:text-primary-500 mb-6 transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Kembali ke Daftar Pesanan
            </button>

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    {{-- Order Items --}}
                    <div class="bg-white rounded-2xl shadow-card p-5">
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-surface-100">
                            <h3 class="font-heading font-bold text-surface-900">Pesanan #{{ $order->order_code }}</h3>
                            <x-status-badge :status="$order->status" />
                        </div>
                        <div class="space-y-4">
                            @foreach($order->items as $item)
                                <div class="flex gap-3 items-center text-sm">
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($item->product->image)<img src="{{ asset('storage/'.$item->product->image) }}" alt="" class="w-full h-full object-cover">@else<svg class="w-6 h-6 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>@endif
                                    </div>
                                    <div class="flex-1 min-w-0"><p class="text-surface-800 font-medium truncate">{{ $item->product->name }}</p><p class="text-surface-500">{{ $item->qty }} x Rp {{ number_format($item->price,0,',','.') }}</p></div>
                                    <p class="font-semibold text-surface-800">Rp {{ number_format($item->price*$item->qty,0,',','.') }}</p>
                                </div>
                            @endforeach
                        </div>
                        <hr class="border-surface-100 my-4">
                        <div class="space-y-2 text-sm text-surface-600">
                            <div class="flex justify-between"><span>Subtotal Produk</span><span class="font-medium text-surface-800">Rp {{ number_format($order->total_price - ($order->shipping_cost ?? 0),0,',','.') }}</span></div>
                            <div class="flex justify-between"><span>Ongkos Kirim @if($order->shipping_zone_label)<span class="text-xs text-surface-400">({{ $order->shipping_zone_label }})</span>@endif</span><span class="font-medium text-surface-800">Rp {{ number_format($order->shipping_cost,0,',','.') }}</span></div>
                            <hr class="border-surface-100 my-2">
                            <div class="flex justify-between font-bold text-surface-900 text-base"><span>Total Bayar</span><span class="text-primary-500">Rp {{ number_format($order->total_price,0,',','.') }}</span></div>
                        </div>
                    </div>

                    @if($order->status === 'shipped')
                    {{-- Live Courier Tracking Map --}}
                    <div class="bg-white rounded-2xl shadow-card p-5">
                        <h3 class="font-heading font-bold text-surface-900 mb-4 flex items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary-500"></span></span>
                            Lacak Kurir Real-Time
                        </h3>
                        @if($order->courier_name)
                            <p class="text-sm text-surface-500 mb-3">Kurir: <span class="font-medium text-surface-800">{{ $order->courier_name }}</span></p>
                        @endif
                        <div wire:ignore id="seller-courier-map-{{ $order->id }}" class="rounded-xl overflow-hidden border border-surface-100" style="height: 480px;"></div>
                        <p class="text-xs text-surface-400 mt-3" id="seller-courier-map-info-{{ $order->id }}">
                            @if($order->courier_lat && $order->courier_lng)
                                Update terakhir: {{ $order->courier_location_updated_at?->format('H:i:s') }} WIB
                            @else
                                Menunggu kurir mulai mengirim lokasi...
                            @endif
                        </p>

                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
                        <script>
                            (function () {
                                const mapId = 'seller-courier-map-{{ $order->id }}';
                                const infoId = 'seller-courier-map-info-{{ $order->id }}';
                                const orderId = {{ $order->id }};
                                const initialLat = @json($order->courier_lat);
                                const initialLng = @json($order->courier_lng);
                                const shippingAddress = @json($order->shipping_address);

                                function initMap() {
                                    const el = document.getElementById(mapId);
                                    if (!el || el.dataset.leafletInited) return;
                                    el.dataset.leafletInited = '1';

                                    const startLat = initialLat ?? -4.95;
                                    const startLng = initialLng ?? 105.0;

                                    // Dikunci ke wilayah Provinsi Lampung (bukan cuma Kab. Lampung Barat) agar kurir tetap terlihat di peta
                                    const lampungBounds = L.latLngBounds([-6.80, 103.30], [-3.40, 106.10]);

                                    const map = L.map(mapId, {
                                        maxBounds: lampungBounds,
                                        maxBoundsViscosity: 0.8,
                                        minZoom: 8,
                                    }).setView([startLat, startLng], 8);

                                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                        attribution: '&copy; OpenStreetMap contributors'
                                    }).addTo(map);

                                    const motorIcon = L.divIcon({
                                        html: '<div style="font-size:24px;line-height:1;">🏍️</div>',
                                        className: '',
                                        iconSize: [28, 28],
                                    });

                                    const houseIcon = L.divIcon({
                                        html: '<div style="font-size:24px;line-height:1;">🏠</div>',
                                        className: '',
                                        iconSize: [28, 28],
                                    });

                                    let marker = (initialLat && initialLng)
                                        ? L.marker([initialLat, initialLng], { icon: motorIcon }).bindPopup('Posisi Kurir').addTo(map)
                                        : null;
                                    let destMarker = null;

                                    function placeDestMarker(lat, lng) {
                                        destMarker = L.marker([lat, lng], { icon: houseIcon })
                                            .bindPopup('Perkiraan Alamat Tujuan')
                                            .addTo(map);

                                        if (marker) {
                                            map.fitBounds(L.latLngBounds([lat, lng], marker.getLatLng()), { padding: [40, 40] });
                                        } else {
                                            map.setView([lat, lng], 12);
                                        }
                                    }

                                    // Cari titik koordinat alamat tujuan (geocoding gratis via OpenStreetMap Nominatim).
                                    // Alamat pekon/desa yang terlalu detail sering tidak ditemukan, jadi coba semakin umum
                                    // (buang bagian paling depan) sampai ketemu, minimal sampai level kabupaten.
                                    function geocodeDestination(parts, startIndex) {
                                        if (startIndex >= parts.length) return;
                                        const query = parts.slice(startIndex).join(', ');

                                        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=id&q=' + encodeURIComponent(query))
                                            .then((res) => res.json())
                                            .then((results) => {
                                                if (results && results.length) {
                                                    placeDestMarker(parseFloat(results[0].lat), parseFloat(results[0].lon));
                                                } else {
                                                    setTimeout(() => geocodeDestination(parts, startIndex + 1), 1100);
                                                }
                                            })
                                            .catch(() => {
                                                setTimeout(() => geocodeDestination(parts, startIndex + 1), 1100);
                                            });
                                    }

                                    if (shippingAddress) {
                                        const addressParts = shippingAddress.split(',').map((s) => s.trim()).filter(Boolean);
                                        geocodeDestination(addressParts, 0);
                                    }

                                    if (window.Echo) {
                                        window.Echo.private('orders.' + orderId)
                                            .listen('.location.updated', (e) => {
                                                if (!marker) {
                                                    marker = L.marker([e.lat, e.lng], { icon: motorIcon }).bindPopup('Posisi Kurir').addTo(map);
                                                } else {
                                                    marker.setLatLng([e.lat, e.lng]);
                                                }
                                                map.panTo([e.lat, e.lng]);
                                                const info = document.getElementById(infoId);
                                                if (info && e.updated_at) {
                                                    info.innerText = 'Update terakhir: ' + new Date(e.updated_at).toLocaleTimeString('id-ID');
                                                }
                                            });
                                    }
                                }

                                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                                    setTimeout(initMap, 0);
                                } else {
                                    document.addEventListener('DOMContentLoaded', initMap);
                                }
                                document.addEventListener('livewire:navigated', initMap);
                            })();
                        </script>
                    </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Customer Info --}}
                    <div class="bg-white rounded-2xl shadow-card p-5">
                        <h3 class="font-heading font-bold text-surface-900 mb-3">Info Customer</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="text-surface-500">Nama:</span> <span class="font-medium text-surface-800">{{ $order->customer->name }}</span></p>
                            <p><span class="text-surface-500">Telepon:</span> <span class="font-medium text-surface-800">{{ $order->shipping_phone ?: $order->customer->phone }}</span></p>
                            <p><span class="text-surface-500">Alamat Kirim:</span></p>
                            <p class="text-surface-600 bg-surface-50 p-2.5 rounded-lg border border-surface-100 mt-1 leading-relaxed">{{ $order->shipping_address }}</p>
                        </div>
                    </div>

                    {{-- Payment Info --}}
                    <div class="bg-white rounded-2xl shadow-card p-5">
                        <h3 class="font-heading font-bold text-surface-900 mb-3">Pembayaran</h3>
                        <div class="space-y-2 text-sm">
                            <p class="flex justify-between"><span class="text-surface-500">Metode:</span><span class="text-surface-800">{{ $order->payment_method === 'cod' ? 'COD (Bayar di Tempat)' : 'Bayar Online' }}</span></p>
                            <p class="flex justify-between gap-3"><span class="text-surface-500">Status:</span><span class="{{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-amber-600' }} font-medium text-right">{{ $order->payment_status === 'paid' ? 'LUNAS' : ($order->payment_method === 'cod' ? 'BAYAR SAAT BARANG DITERIMA' : 'BELUM BAYAR') }}</span></p>
                            @if($order->isPaid() && $order->transaction)
                                @php
                                    $ch = $order->transaction->xendit_payment_channel ?? '';
                                    $pm = $order->transaction->xendit_payment_method ?? '';
                                    $chLabel = match(true) {
                                        str_contains(strtoupper($ch), 'BCA')          => 'BCA Virtual Account',
                                        str_contains(strtoupper($ch), 'BRI')          => 'BRI Virtual Account',
                                        str_contains(strtoupper($ch), 'BNI')          => 'BNI Virtual Account',
                                        str_contains(strtoupper($ch), 'MANDIRI')      => 'Mandiri Virtual Account',
                                        str_contains(strtoupper($ch), 'BSI')          => 'BSI Virtual Account',
                                        str_contains(strtoupper($pm.$ch), 'QRIS')     => 'QRIS',
                                        str_contains(strtoupper($pm.$ch), 'GOPAY')    => 'GoPay',
                                        str_contains(strtoupper($pm.$ch), 'DANA')     => 'DANA',
                                        str_contains(strtoupper($pm.$ch), 'OVO')      => 'OVO',
                                        str_contains(strtoupper($pm.$ch), 'SHOPEEPAY') => 'ShopeePay',
                                        $ch !== '' => $ch,
                                        default => 'Bayar Online',
                                    };
                                @endphp
                                <p class="flex justify-between items-center"><span class="text-surface-500">Via:</span><span class="text-xs font-semibold px-2 py-0.5 bg-primary-50 text-primary-700 rounded-full">{{ $chLabel }}</span></p>
                                <p class="flex justify-between"><span class="text-surface-500">Dibayar pada:</span><span class="text-surface-800 text-xs">{{ $order->paid_at?->format('d M Y, H:i') ?? '-' }}</span></p>
                            @elseif($order->isPaid())
                                <p class="flex justify-between"><span class="text-surface-500">Dibayar pada:</span><span class="text-surface-800 text-xs">{{ $order->paid_at?->format('d M Y, H:i') ?? '-' }}</span></p>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-3">
                        {{-- Status-based Actions --}}
                        <div class="bg-white rounded-2xl shadow-card p-5">
                            <h3 class="font-heading font-bold text-surface-900 mb-3">Tindakan</h3>

                            @if($order->status === 'waiting_payment')
                                <p class="text-sm text-surface-500">Menunggu customer melakukan pembayaran. Pembayaran akan terkonfirmasi otomatis setelah customer selesai bayar.</p>
                            @elseif($order->status === 'processing')
                                @if($order->payment_method === 'cod')
                                    <p class="text-xs text-surface-500 mb-4">Pesanan COD siap diproses. Tugaskan kurir untuk mengantar pesanan dan menagih pembayaran saat barang diterima; link pelacakan akan dikirim otomatis via WhatsApp.</p>
                                @else
                                    <p class="text-xs text-surface-500 mb-4">Pembayaran sudah dikonfirmasi. Tugaskan kurir untuk mengantar pesanan ini; link pelacakan akan dikirim otomatis via WhatsApp.</p>
                                @endif
                                <form wire:submit.prevent="sendCourierAccess" class="space-y-3">
                                    <div>
                                        <input type="text" wire:model="courierName" placeholder="Nama Kurir" class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100">
                                        @error('courierName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <input type="text" wire:model="courierPhone" placeholder="No. WhatsApp Kurir (08xxxxxxxxxx)" class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100">
                                        @error('courierPhone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="submit" class="w-full py-2.5 bg-blue-500 text-white font-bold rounded-xl hover:bg-blue-600 transition-all text-sm shadow-md">
                                        <span wire:loading.remove wire:target="sendCourierAccess">Kirim Akses Kurir</span>
                                        <span wire:loading wire:target="sendCourierAccess">Memproses...</span>
                                    </button>
                                </form>
                            @elseif($order->status === 'shipped')
                                @if($editingCourier)
                                    <form wire:submit.prevent="updateCourierAccess" class="space-y-3">
                                        <p class="text-xs text-surface-500">Perbaiki data kurir jika salah ketik nama/nomor. Link pelacakan lama akan dihanguskan & link baru dikirim ulang via WhatsApp.</p>
                                        <div>
                                            <input type="text" wire:model="courierName" placeholder="Nama Kurir" class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100">
                                            @error('courierName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <input type="text" wire:model="courierPhone" placeholder="No. WhatsApp Kurir (08xxxxxxxxxx)" class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100">
                                            @error('courierPhone') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" wire:click="cancelEditCourier" class="flex-1 py-2.5 bg-surface-100 text-surface-600 font-semibold rounded-xl hover:bg-surface-200 transition-all text-sm">Batal</button>
                                            <button type="submit" class="flex-1 py-2.5 bg-blue-500 text-white font-bold rounded-xl hover:bg-blue-600 transition-all text-sm shadow-md">
                                                <span wire:loading.remove wire:target="updateCourierAccess">Simpan & Kirim Ulang</span>
                                                <span wire:loading wire:target="updateCourierAccess">Memproses...</span>
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="space-y-3">
                                        <div class="p-3 bg-surface-50 border border-surface-100 rounded-xl text-sm">
                                            <p><span class="text-surface-500">Kurir:</span> <span class="font-medium text-surface-800">{{ $order->courier_name }}</span></p>
                                            <p><span class="text-surface-500">No. WA:</span> <span class="font-medium text-surface-800">{{ $order->courier_phone }}</span></p>
                                            <p class="mt-1">
                                                @if($order->courier_token)
                                                    <span class="text-xs font-semibold px-2 py-0.5 bg-blue-50 text-blue-600 rounded-full">Link pelacakan aktif</span>
                                                @else
                                                    <span class="text-xs font-semibold px-2 py-0.5 bg-surface-200 text-surface-600 rounded-full">Link sudah tidak berlaku</span>
                                                @endif
                                            </p>
                                        </div>
                                        <p class="text-xs text-surface-500">Barang dalam pengiriman. Status akan otomatis menjadi "Selesai" saat kurir menyelesaikan pengantaran di halamannya.</p>
                                        @if($order->courier_token)
                                            <button wire:click="editCourierAccess" class="w-full py-2.5 bg-white border border-surface-300 text-surface-700 font-semibold rounded-xl hover:bg-surface-50 transition-all text-sm">Edit Info Kurir (Salah Ketik?)</button>
                                        @endif
                                        <button wire:click="completeOrder" wire:confirm="Tandai pesanan ini selesai secara manual?" class="w-full py-2.5 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 transition-all text-sm shadow-md">Tandai Selesai (Manual)</button>
                                    </div>
                                @endif
                            @elseif($order->status === 'delivered')
                                <div class="p-3 bg-green-50 text-green-700 border border-green-200 rounded-xl text-center text-sm font-semibold">Transaksi Selesai</div>
                            @elseif($order->status === 'cancelled')
                                <div class="p-3 bg-red-50 text-red-700 border border-red-200 rounded-xl text-center text-sm font-semibold">Dibatalkan</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
