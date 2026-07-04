<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{ paymentQrPreview: null }">
    <h1 class="font-heading text-2xl font-bold text-surface-900 mb-6">Checkout</h1>

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">
            {{-- 🔒 SECURITY FIX: Escaped output (ISSUE-008) --}}
            {{ session('error') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            {{-- Shipping Address --}}
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h3 class="font-heading font-bold text-surface-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Alamat Pengiriman
                </h3>
                <div class="p-4 bg-surface-50 border border-surface-200 rounded-xl space-y-2">
                    <p class="font-semibold text-surface-800 text-sm">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-surface-500">{{ Auth::user()->phone }}</p>
                    <p class="text-sm text-surface-600 leading-relaxed">{{ $shippingAddress ?: 'Alamat belum diatur dalam profil Anda.' }}</p>
                    @error('shippingAddress') <span class="text-xs text-red-600 block mt-2">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Grouped Items --}}
            @php $grouped = $this->groupedItems; @endphp
            @forelse($grouped as $storeId => $data)
                <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                    <div class="px-5 py-3 bg-surface-50 border-b border-surface-100 flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-xs font-bold text-primary-600">{{ strtoupper(substr($data['store']->name, 0, 1)) }}</span>
                        </div>
                        <span class="font-semibold text-sm text-surface-800">{{ $data['store']->name }}</span>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- Items --}}
                        @foreach($data['items'] as $item)
                            <div class="flex gap-3 items-center text-sm">
                                <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center shrink-0 overflow-hidden">
                                    @if($item['product']->image)
                                        <img src="{{ asset('storage/' . $item['product']->image) }}" alt="{{ $item['product']->name }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-surface-800 font-medium truncate">{{ $item['product']->name }}</p>
                                    <p class="text-surface-500">{{ $item['qty'] }} x Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                </div>
                                <p class="font-semibold text-surface-800">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                            </div>
                        @endforeach

                        <hr class="border-surface-100">

                        {{-- Payment Method --}}
                        <div>
                            <h4 class="text-xs font-semibold text-surface-500 uppercase tracking-wider mb-3">Metode Pembayaran Toko Ini</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_{{ $storeId }}" value="xendit" wire:model="paymentMethods.{{ $storeId }}" class="peer hidden">
                                    <div class="p-3 rounded-xl border-2 text-center text-sm font-medium transition-all
                                                peer-checked:border-primary-500 peer-checked:bg-primary-50/50 peer-checked:text-primary-600
                                                border-surface-200 text-surface-600 hover:border-surface-300">
                                        <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        Bayar Online
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_{{ $storeId }}" value="cod" wire:model="paymentMethods.{{ $storeId }}" class="peer hidden">
                                    <div class="p-3 rounded-xl border-2 text-center text-sm font-medium transition-all
                                                peer-checked:border-primary-500 peer-checked:bg-primary-50/50 peer-checked:text-primary-600
                                                border-surface-200 text-surface-600 hover:border-surface-300">
                                        <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        COD (Bayar di Tempat)
                                    </div>
                                </label>
                            </div>

                            @if(($paymentMethods[$storeId] ?? 'xendit') === 'xendit')
                                <div class="mt-3 p-3 bg-blue-50 rounded-xl border border-blue-200">
                                    <p class="text-xs text-blue-700">Bayar via Virtual Account bank (BCA, BRI, BNI, Mandiri), QRIS, atau dompet digital (GoPay, DANA, OVO, ShopeePay). Pembayaran dikonfirmasi otomatis.</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-between text-sm font-semibold text-surface-800 pt-2">
                            <span>Subtotal Toko</span>
                            <span>Rp {{ number_format($data['subtotal'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-card p-8 text-center">
                    <p class="text-surface-500">Membaca data keranjang...</p>
                </div>
            @endforelse
        </div>

        {{-- Summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-card p-5 sticky top-24">
                <h3 class="font-heading font-bold text-surface-900 mb-4">Ringkasan Pesanan</h3>

                @php
                    $subtotal = 0;
                    foreach($grouped as $storeId => $data) {
                        $subtotal += $data['subtotal'];
                    }
                    $total = $subtotal;
                @endphp

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-surface-600">
                        <span>Subtotal Produk</span>
                        <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-surface-600">
                        <span>Ongkos Kirim</span>
                        <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Menunggu Konfirmasi</span>
                    </div>
                    <hr class="border-surface-100">
                    <div class="flex justify-between font-bold text-surface-900 text-lg">
                        <span>Total Belanja</span>
                        <span class="text-primary-500">Rp {{ number_format($total, 0, ',', '.') }}</span>
                    </div>
                    <p class="text-[10px] text-surface-500 italic mt-1 text-right">*Belum termasuk ongkos kirim (akan diisi oleh penjual setelah pesanan dibuat).</p>
                </div>
                <button wire:click="placeOrder" wire:loading.attr="disabled" class="mt-5 w-full flex items-center justify-center gap-2 py-3 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="placeOrder">Konfirmasi Pesanan</span>
                    <span wire:loading wire:target="placeOrder" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span wire:loading wire:target="placeOrder">Memproses...</span>
                </button>
                <p class="text-xs text-surface-400 text-center mt-3">Dengan mengkonfirmasi, Anda menyetujui Syarat & Ketentuan yang berlaku.</p>
            </div>
        </div>
    </div>

    <div x-show="paymentQrPreview" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="paymentQrPreview = null" @keydown.escape.window="paymentQrPreview = null">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-4 shadow-2xl">
            <button type="button" @click="paymentQrPreview = null" class="absolute right-3 top-3 rounded-full bg-white/90 p-2 text-surface-600 shadow hover:text-surface-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <img :src="paymentQrPreview" alt="Preview QR pembayaran" class="max-h-[75vh] w-full object-contain rounded-xl bg-surface-50">
        </div>
    </div>
</div>
