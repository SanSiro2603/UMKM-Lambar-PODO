<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="font-heading text-2xl font-bold text-surface-900 mb-6">Pesanan Saya</h1>

    {{-- Status Tabs --}}
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        @foreach(['semua' => 'Semua', 'menunggu' => 'Menunggu', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'delivered' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $key => $label)
            <button wire:click="selectTab('{{ $key }}')"
                    class="px-4 py-2 rounded-xl text-sm font-medium border whitespace-nowrap transition-all
                           {{ $statusTab === $key ? 'bg-primary-500 text-white border-primary-500' : 'bg-white text-surface-600 hover:bg-surface-50 border-surface-200' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Orders --}}
    <div class="space-y-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-2xl shadow-card overflow-hidden hover:shadow-card-hover transition-shadow">
                <div class="px-5 py-3 bg-surface-50 border-b border-surface-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="font-medium text-sm text-surface-800">#{{ $order->order_code }}</span>
                        <span class="text-surface-300">|</span>
                        <span class="text-sm text-surface-500">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <x-status-badge :status="$order->status" />
                </div>
                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="font-semibold text-surface-800">{{ $order->store->name }}</p>
                        <p class="text-sm text-surface-500 mt-1">
                            @foreach($order->items as $item)
                                {{ $item->product->name }} (x{{ $item->qty }}){{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </p>
                        <p class="text-xs text-surface-400 mt-1">Metode: {{ $order->payment_method === 'xendit' ? 'Bayar Online' : 'COD (Bayar di Tempat)' }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <p class="font-heading font-bold text-primary-500 text-lg">Rp {{ number_format($order->total_price + 5000, 0, ',', '.') }}</p>
                        <a href="{{ url('/customer/orders/' . $order->id) }}" wire:navigate class="px-4 py-2 text-sm font-medium text-primary-500 border border-primary-500 rounded-lg hover:bg-primary-50 transition-colors whitespace-nowrap">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl shadow-card p-12 text-center">
                <p class="text-surface-500 font-medium">Belum ada pesanan dalam kategori ini.</p>
                <a href="{{ url('/products') }}" wire:navigate class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-all text-sm">Mulai Belanja</a>
            </div>
        @endforelse
    </div>
</div>
