<div class="p-5" x-data="courierPage({{ $trackingActive ? 'true' : 'false' }})" x-init="init()">
    @if($invalid)
        <div class="p-6 bg-red-50 border border-red-200 rounded-2xl text-center mt-6">
            <svg class="w-10 h-10 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <p class="font-semibold text-red-700">Link Tidak Valid</p>
            <p class="text-sm text-red-500 mt-1">Link ini sudah kadaluarsa, sudah digunakan, atau pesanan tidak sedang dalam pengiriman.</p>
        </div>
    @elseif($justCompleted)
        <div class="p-6 bg-green-50 border border-green-200 rounded-2xl text-center mt-6">
            <svg class="w-10 h-10 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="font-semibold text-green-700">Pengantaran Selesai</p>
            <p class="text-sm text-green-600 mt-1">Terima kasih! Pesanan #{{ $order->order_code }} telah ditandai selesai. Link ini sudah tidak berlaku lagi.</p>
        </div>
    @else
        <div class="space-y-4">
            {{-- Buyer Info --}}
            <div class="p-4 bg-surface-50 border border-surface-200 rounded-xl">
                <h3 class="font-heading font-bold text-surface-900 mb-2">Pesanan #{{ $order->order_code }}</h3>
                <p class="text-sm text-surface-500">Nama Pembeli</p>
                <p class="font-medium text-surface-800 mb-2">{{ $order->customer->name }}</p>
                <p class="text-sm text-surface-500">Alamat Lengkap Tujuan</p>
                <p class="font-medium text-surface-800 mb-3 leading-relaxed">{{ $order->shipping_address }}</p>

                <a href="tel:{{ $order->shipping_phone ?: $order->customer->phone }}" class="w-full flex items-center justify-center gap-2 py-2.5 bg-primary-500 text-white rounded-xl font-semibold text-sm hover:bg-primary-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Telepon Pembeli ({{ $order->shipping_phone ?: $order->customer->phone }})
                </a>
            </div>

            {{-- COD Banner --}}
            @if($order->payment_method === 'cod')
                <div class="p-4 bg-amber-50 border-2 border-amber-300 rounded-xl text-center">
                    <p class="font-bold text-amber-800">PESANAN COD!</p>
                    <p class="font-bold text-amber-800 text-lg mt-1">Tagih uang tunai sebesar: Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                </div>
            @endif

            {{-- GPS Tracking Controls --}}
            <div class="p-4 bg-white border border-surface-200 rounded-xl">
                <p class="text-xs text-surface-500 mb-3" x-show="!tracking">Klik tombol di bawah untuk mengizinkan akses lokasi dan mulai mengirim posisi Anda ke pembeli.</p>
                <p class="text-xs text-blue-600 font-medium mb-3 flex items-center gap-2" x-show="tracking" x-cloak>
                    <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span></span>
                    Lokasi Anda sedang dibagikan ke pembeli...
                </p>

                <button type="button" x-show="!tracking" @click="startDelivery()" class="w-full py-3 bg-blue-500 text-white rounded-xl font-bold hover:bg-blue-600 transition-colors">
                    Mulai Antar
                </button>

                <button type="button" x-show="tracking" x-cloak @click="finish()" class="w-full py-3 bg-green-500 text-white rounded-xl font-bold hover:bg-green-600 transition-colors">
                    {{ $order->payment_method === 'cod' ? 'Barang Sampai & Uang Diterima' : 'Barang Sampai' }}
                </button>
            </div>
        </div>
    @endif
</div>

<script>
function courierPage(initialTracking) {
    return {
        tracking: initialTracking,
        intervalId: null,
        init() {
            if (this.tracking) {
                this.startWatching();
            }
        },
        startDelivery() {
            this.tracking = true;
            this.$wire.startDelivery();
            this.startWatching();
        },
        startWatching() {
            if (this.intervalId) return;
            this.sendPosition();
            this.intervalId = setInterval(() => this.sendPosition(), 30000);
        },
        stopWatching() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
                this.intervalId = null;
            }
        },
        sendPosition() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    this.$wire.updateLocation(pos.coords.latitude, pos.coords.longitude);
                },
                (err) => console.warn('Gagal mengambil lokasi GPS', err),
                { enableHighAccuracy: true, timeout: 20000 }
            );
        },
        finish() {
            if (!confirm('Konfirmasi barang sudah sampai ke pembeli?')) return;
            this.$wire.completeDelivery().then(() => {
                this.tracking = false;
                this.stopWatching();
            });
        }
    };
}
</script>
