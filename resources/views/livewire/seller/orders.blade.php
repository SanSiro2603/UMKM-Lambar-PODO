<div>
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
                @foreach(['semua' => 'Semua', 'waiting_shipping_cost' => 'Menunggu Ongkir', 'waiting_payment' => 'Menunggu Bayar', 'paid' => 'Dibayar', 'shipped' => 'Dikirim', 'delivered' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $key => $label)
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
                            <div class="flex justify-between"><span>Ongkos Kirim</span><span class="font-medium text-surface-800">@if(is_null($order->shipping_cost))<span class="text-amber-600 italic">Belum ditentukan</span>@else Rp {{ number_format($order->shipping_cost,0,',','.') }}@endif</span></div>
                            <hr class="border-surface-100 my-2">
                            <div class="flex justify-between font-bold text-surface-900 text-base"><span>Total Bayar</span><span class="text-primary-500">Rp {{ number_format($order->total_price,0,',','.') }}</span></div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Customer Info --}}
                    <div class="bg-white rounded-2xl shadow-card p-5">
                        <h3 class="font-heading font-bold text-surface-900 mb-3">Info Customer</h3>
                        <div class="space-y-2 text-sm">
                            <p><span class="text-surface-500">Nama:</span> <span class="font-medium text-surface-800">{{ $order->customer->name }}</span></p>
                            <p><span class="text-surface-500">Telepon:</span> <span class="font-medium text-surface-800">{{ $order->customer->phone }}</span></p>
                            <p><span class="text-surface-500">Alamat Kirim:</span></p>
                            <p class="text-surface-600 bg-surface-50 p-2.5 rounded-lg border border-surface-100 mt-1 leading-relaxed">{{ $order->shipping_address }}</p>
                        </div>
                    </div>

                    {{-- Payment Info --}}
                    <div class="bg-white rounded-2xl shadow-card p-5">
                        <h3 class="font-heading font-bold text-surface-900 mb-3">Pembayaran</h3>
                        <div class="space-y-2 text-sm">
                            <p class="flex justify-between"><span class="text-surface-500">Metode:</span><span class="text-surface-800">{{ $order->payment_method === 'cod' ? 'COD (Bayar di Tempat)' : 'Bayar Online' }}</span></p>
                            <p class="flex justify-between"><span class="text-surface-500">Status:</span><span class="{{ $order->payment_status === 'paid' ? 'text-green-600' : 'text-amber-600' }} font-medium">{{ $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM BAYAR' }}</span></p>
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
                        {{-- Set Shipping Cost --}}
                        @if(is_null($order->shipping_cost))
                        <div class="bg-white rounded-2xl shadow-card p-5">
                            <h3 class="font-heading font-bold text-surface-900 mb-2">Tentukan Ongkos Kirim</h3>
                            <p class="text-xs text-surface-500 mb-4">Masukkan tarif pengiriman agar customer bisa melanjutkan pembayaran.</p>
                            <form wire:submit.prevent="setShippingCost" class="space-y-3">
                                <div>
                                    <div class="relative rounded-xl shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><span class="text-surface-500 text-sm">Rp</span></div>
                                        <input type="number" wire:model="inputShippingCost" placeholder="Contoh: 15000" class="w-full pl-9 pr-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100">
                                    </div>
                                    @error('inputShippingCost') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <button type="submit" class="w-full py-2.5 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all text-sm shadow-md">Simpan Ongkir</button>
                            </form>
                        </div>
                        @endif

                        {{-- Status-based Actions --}}
                        <div class="bg-white rounded-2xl shadow-card p-5">
                            <h3 class="font-heading font-bold text-surface-900 mb-3">Tindakan</h3>

                            @if(is_null($order->shipping_cost))
                                <p class="text-sm text-surface-500 italic">Tentukan ongkos kirim terlebih dahulu.</p>
                            @elseif($order->status === 'waiting_payment')
                                <p class="text-sm text-surface-500">Menunggu customer melakukan pembayaran. Pembayaran akan terkonfirmasi otomatis setelah customer selesai bayar.</p>
                            @elseif($order->status === 'paid')
                                <p class="text-xs text-surface-500 mb-4">Pembayaran sudah dikonfirmasi. Silakan proses pesanan dan kirim ke customer.</p>
                                <button wire:click="shipOrder" class="w-full py-2.5 bg-blue-500 text-white font-bold rounded-xl hover:bg-blue-600 transition-all text-sm shadow-md">Tandai Telah Dikirim</button>
                            @elseif($order->status === 'shipped')
                                <p class="text-xs text-surface-500 mb-4">Barang dalam pengiriman. Tunggu customer konfirmasi atau selesaikan manual.</p>
                                <button wire:click="completeOrder" class="w-full py-2.5 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600 transition-all text-sm shadow-md">Tandai Selesai</button>
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