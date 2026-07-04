<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ url('/customer/orders') }}" wire:navigate class="p-2 rounded-lg hover:bg-surface-100 transition-colors">
                <svg class="w-5 h-5 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="font-heading text-xl font-bold text-surface-900">Pesanan #{{ $order->order_code }}</h1>
                <p class="text-sm text-surface-500">{{ $order->created_at->format('d F Y, H:i') }} WIB</p>
            </div>
        </div>
        <div>
            @if(in_array($order->status, ['waiting_shipping_cost', 'waiting_payment']))
                <button wire:click="cancelOrder" wire:confirm="Apakah Anda yakin ingin membatalkan pesanan ini?" class="px-4 py-2 bg-red-50 text-red-600 border border-red-200 rounded-xl hover:bg-red-100 text-sm font-semibold transition-all">Batalkan Pesanan</button>
            @endif
            @if($order->status === 'shipped')
                <button wire:click="confirmReceived" wire:confirm="Pastikan Anda telah menerima barang dengan baik." class="px-5 py-2.5 bg-primary-500 text-white rounded-xl hover:bg-primary-600 text-sm font-semibold transition-all shadow-md">Pesanan Diterima</button>
            @endif
        </div>
    </div>

    @if(session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">{{ session('error') }}</div>
    @endif

    {{-- Status Timeline --}}
    <div class="bg-white rounded-2xl shadow-card p-5 mb-6">
        <h3 class="font-heading font-bold text-surface-900 mb-5">Tracking Pesanan</h3>
        @if($order->status === 'cancelled')
            <div class="p-5 bg-red-50 border border-red-200 rounded-xl text-center">
                <svg class="w-10 h-10 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <p class="font-semibold text-red-700">Pesanan Dibatalkan</p>
                <p class="text-sm text-red-500 mt-1">Pesanan ini telah dibatalkan.</p>
            </div>
        @else
        <div class="space-y-0">
                @php
                    $statusFlow = [
                        'waiting_shipping_cost' => ['label' => 'Pesanan Dibuat', 'desc' => 'Menunggu penjual menentukan ongkos kirim'],
                        'waiting_payment'       => ['label' => 'Menunggu Pembayaran', 'desc' => 'Ongkir sudah ditentukan, silakan lakukan pembayaran'],
                        'paid'                  => ['label' => 'Dibayar / Diproses', 'desc' => 'Pembayaran berhasil, pesanan sedang diproses penjual'],
                        'shipped'               => ['label' => 'Dibawa Kurir', 'desc' => 'Pesanan dalam perjalanan ke alamat Anda'],
                        'delivered'             => ['label' => 'Selesai', 'desc' => 'Pesanan telah diterima. Terima kasih!'],
                    ];

                    $visibleStatuses = array_keys($statusFlow);
                    $currentIdx = array_search($order->status, $visibleStatuses);
                @endphp

                @foreach($statusFlow as $status => $step)
                    @php
                        $stepIdx = array_search($status, $visibleStatuses);
                        $isDone = $stepIdx < $currentIdx;
                        $isCurrent = $stepIdx === $currentIdx;
                        $isLastAndCurrent = $isCurrent && $loop->last;
                    @endphp
                    <div class="flex gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 {{ ($isDone || $isLastAndCurrent) ? 'bg-primary-500' : ($isCurrent ? 'bg-primary-100 border-2 border-primary-500' : 'bg-surface-200') }}">
                                @if($isDone || $isLastAndCurrent)
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @elseif($isCurrent)
                                    <div class="w-3 h-3 rounded-full bg-primary-500 animate-pulse"></div>
                                @else
                                    <svg class="w-5 h-5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                            @if(!$loop->last)
                                <div class="w-0.5 h-8 {{ $isDone ? 'bg-primary-400' : 'bg-surface-200' }}"></div>
                            @endif
                        </div>
                        <div class="pb-6 {{ $loop->last ? 'pb-0' : '' }}">
                            <p class="font-semibold text-sm {{ ($isDone || $isLastAndCurrent) ? 'text-primary-600' : ($isCurrent ? 'text-surface-900' : 'text-surface-400') }}">
                                {{ $step['label'] }}
                                @if($isCurrent)
                                    <span class="ml-2 text-[10px] bg-primary-100 text-primary-700 px-2 py-0.5 rounded-full font-medium">Saat Ini</span>
                                @endif
                            </p>
                            <p class="text-xs {{ $isCurrent ? 'text-surface-600' : 'text-surface-400' }} mt-0.5">{{ $step['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Items --}}
            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-3 bg-surface-50 border-b border-surface-100 flex items-center gap-2"><span class="font-semibold text-sm text-surface-800">{{ $order->store->name }}</span></div>
                <div class="divide-y divide-surface-50">
                    @foreach($order->items as $item)
                        <div class="p-5 flex gap-4 items-center">
                            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center shrink-0 overflow-hidden">
                                @if($item->product->image)<img src="{{ asset('storage/'.$item->product->image) }}" alt="" class="w-full h-full object-cover">@else<svg class="w-7 h-7 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>@endif
                            </div>
                            <div class="flex-1"><p class="font-medium text-surface-800 text-sm">{{ $item->product->name }}</p><p class="text-sm text-surface-500 mt-0.5">{{ $item->qty }} x Rp {{ number_format($item->price,0,',','.') }}</p></div>
                            <p class="font-semibold text-surface-800 text-sm">Rp {{ number_format($item->price*$item->qty,0,',','.') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Tombol Bayar --}}
            @if($order->canPay())
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h3 class="font-heading font-bold text-surface-900 mb-3">Lakukan Pembayaran</h3>
                <p class="text-sm text-surface-600 mb-4">Total yang harus dibayar: <strong class="text-primary-500 text-lg">Rp {{ number_format($order->total_price,0,',','.') }}</strong></p>

                {{-- Pilihan metode yang tersedia --}}
                <div class="mb-4 p-3 bg-surface-50 rounded-xl border border-surface-100">
                    <p class="text-xs font-semibold text-surface-500 mb-2">Pilih metode pembayaran saat klik tombol bayar:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['BCA VA', 'BRI VA', 'BNI VA', 'Mandiri VA', 'BSI VA'] as $m)
                            <span class="text-xs px-2 py-0.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg font-medium">{{ $m }}</span>
                        @endforeach
                        <span class="text-xs px-2 py-0.5 bg-purple-50 text-purple-600 border border-purple-100 rounded-lg font-medium">QRIS</span>
                        @foreach(['GoPay', 'DANA', 'OVO', 'ShopeePay'] as $m)
                            <span class="text-xs px-2 py-0.5 bg-green-50 text-green-600 border border-green-100 rounded-lg font-medium">{{ $m }}</span>
                        @endforeach
                    </div>
                </div>

                <button wire:click="payWithXendit" class="w-full py-3 bg-primary-500 text-white rounded-xl font-semibold hover:bg-primary-600 transition-colors">
                    <span wire:loading.remove wire:target="payWithXendit">Bayar Sekarang</span>
                    <span wire:loading wire:target="payWithXendit" class="flex items-center justify-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...</span>
                </button>

                @if($order->xendit_invoice_id && $order->payment_status === 'unpaid')
                <button wire:click="checkPaymentStatus" class="w-full py-2 mt-2 border border-surface-300 text-surface-600 bg-white rounded-xl text-sm font-semibold hover:bg-surface-50 transition-colors">
                    <span wire:loading.remove wire:target="checkPaymentStatus">Sudah Bayar? Cek Status</span>
                    <span wire:loading wire:target="checkPaymentStatus" class="flex items-center justify-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Mengecek...</span>
                </button>
                @endif
            </div>
            @elseif($order->status === 'waiting_shipping_cost')
            <div class="bg-white rounded-2xl shadow-card p-5">
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm flex items-start gap-2">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <div><span class="font-semibold block mb-1">Menunggu Penjual Menentukan Ongkos Kirim</span>Silakan cek kembali nanti. Tombol bayar akan muncul setelah ongkir ditentukan.</div>
                </div>
            </div>
            @elseif($order->status === 'paid')
            <div class="bg-white rounded-2xl shadow-card p-5">
                <div class="border-2 border-dashed border-green-200 rounded-xl p-6 text-center bg-green-50/20">
                    <div class="w-12 h-12 mx-auto rounded-full bg-green-50 flex items-center justify-center mb-3"><svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                    <p class="text-sm text-green-700 font-medium">Pembayaran berhasil dikonfirmasi!</p>
                    <p class="text-xs text-surface-400 mt-1">Menunggu penjual memproses pesanan Anda.</p>
                </div>
            </div>
            @elseif($order->status === 'cancelled')
            <div class="bg-white rounded-2xl shadow-card p-5">
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm font-medium">Pesanan telah dibatalkan.</div>
            </div>
            @elseif($order->status === 'delivered')
            {{-- Rating Section --}}
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h3 class="font-heading font-bold text-surface-900 mb-4">Beri Rating Produk</h3>
                <p class="text-sm text-surface-500 mb-5">Bagikan pengalaman Anda untuk setiap produk yang dibeli.</p>
                <div class="space-y-6">
                    @foreach($order->items as $item)
                        @php $pid = $item->product_id; @endphp
                        <div class="p-4 border border-surface-200 rounded-xl">
                            <div class="flex gap-3 items-start">
                                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center shrink-0 overflow-hidden">
                                    @if($item->product->image)
                                        <img src="{{ asset('storage/'.$item->product->image) }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-sm text-surface-800">{{ $item->product->name }}</p>
                                </div>
                            </div>

                            @php $rated = $this->hasRated($pid); @endphp
                            @if($rated)
                                <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Rating sudah dikirim
                                </div>
                            @else
                                <form wire:submit.prevent="submitRating({{ $pid }})" class="mt-3 space-y-3">
                                    {{-- Star Rating --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-surface-500 mb-1.5">Rating</label>
                                        <div class="flex gap-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <button type="button" wire:click="setRating({{ $pid }}, {{ $i }})" class="transition-all {{ ($ratingInputs[$pid] ?? 0) >= $i ? 'text-yellow-400' : 'text-surface-300' }}">
                                                    <svg class="w-7 h-7 fill-current" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                </button>
                                            @endfor
                                            @if(($ratingInputs[$pid] ?? 0) > 0)
                                                <span class="ml-2 text-sm text-surface-500">{{ $ratingInputs[$pid] }} bintang</span>
                                            @endif
                                        </div>
                                        @error("ratingInputs.{$pid}") <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Comment --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-surface-500 mb-1.5">Komentar (opsional)</label>
                                        <textarea wire:model="commentInputs.{{ $pid }}" rows="2" placeholder="Bagikan pengalaman Anda..." class="w-full px-3 py-2 border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-1 focus:ring-primary-100 resize-none"></textarea>
                                    </div>

                                    <button type="submit" class="px-4 py-2 bg-primary-500 text-white text-sm font-semibold rounded-xl hover:bg-primary-600 transition-colors">Kirim Rating</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Payment Info --}}
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h3 class="font-heading font-bold text-surface-900 mb-4">Info Pembayaran</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-surface-500">Metode</span><span class="font-medium text-surface-800">{{ $order->payment_method === 'cod' ? 'COD (Bayar di Tempat)' : 'Bayar Online' }}</span></div>
                    <div class="flex justify-between"><span class="text-surface-500">Status</span><x-status-badge status="{{ $order->payment_status }}" /></div>
                    @if($order->transaction && $order->transaction->isPaid())
                        @php
                            $ch = $order->transaction->xendit_payment_channel ?? '';
                            $pm = $order->transaction->xendit_payment_method ?? '';
                            $channelLabel = match(true) {
                                str_contains(strtoupper($ch), 'BCA')     => 'BCA Virtual Account',
                                str_contains(strtoupper($ch), 'BRI')     => 'BRI Virtual Account',
                                str_contains(strtoupper($ch), 'BNI')     => 'BNI Virtual Account',
                                str_contains(strtoupper($ch), 'MANDIRI') => 'Mandiri Virtual Account',
                                str_contains(strtoupper($ch), 'BSI')     => 'BSI Virtual Account',
                                str_contains(strtoupper($ch), 'PERMATA') => 'Permata Virtual Account',
                                str_contains(strtoupper($pm.$ch), 'QRIS') => 'QRIS',
                                str_contains(strtoupper($pm.$ch), 'GOPAY') => 'GoPay',
                                str_contains(strtoupper($pm.$ch), 'DANA')  => 'DANA',
                                str_contains(strtoupper($pm.$ch), 'OVO')   => 'OVO',
                                str_contains(strtoupper($pm.$ch), 'SHOPEEPAY') => 'ShopeePay',
                                str_contains(strtoupper($pm.$ch), 'LINKAJA')   => 'LinkAja',
                                $ch !== '' => $ch,
                                default => 'Bayar Online',
                            };
                        @endphp
                        <div class="flex justify-between items-center">
                            <span class="text-surface-500">Via</span>
                            <span class="text-xs font-semibold px-2 py-0.5 bg-primary-50 text-primary-700 rounded-full">{{ $channelLabel }}</span>
                        </div>
                        @if($order->paid_at)
                            <div class="flex justify-between"><span class="text-surface-500">Waktu Bayar</span><span class="text-surface-700 text-xs">{{ $order->paid_at->format('d M Y, H:i') }} WIB</span></div>
                        @endif
                    @endif
                    <div class="flex justify-between"><span class="text-surface-500">Subtotal Produk</span><span class="text-surface-800">Rp {{ number_format($order->total_price - ($order->shipping_cost ?? 0),0,',','.') }}</span></div>
                    <div class="flex justify-between"><span class="text-surface-500">Ongkos Kirim</span><span class="text-surface-800">@if(is_null($order->shipping_cost))<span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Menunggu Konfirmasi</span>@else Rp {{ number_format($order->shipping_cost,0,',','.') }}@endif</span></div>
                    <hr class="border-surface-100">
                    <div class="flex justify-between font-bold text-base"><span class="text-surface-900">Total</span><span class="text-primary-500">Rp {{ number_format($order->total_price,0,',','.') }}</span></div>
                </div>
            </div>

            {{-- Shipping Info --}}
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h3 class="font-heading font-bold text-surface-900 mb-4">Alamat Pengiriman</h3>
                <p class="text-sm font-medium text-surface-800">{{ $order->customer->name }}</p>
                <p class="text-sm text-surface-600 mt-1">{{ $order->customer->phone }}</p>
                <p class="text-sm text-surface-500 mt-2">{{ $order->shipping_address }}</p>
            </div>
        </div>
    </div>
</div>