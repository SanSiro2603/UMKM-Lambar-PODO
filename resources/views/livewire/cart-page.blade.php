<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="font-heading text-2xl font-bold text-surface-900 mb-6">Keranjang Belanja</h1>

    {{-- Empty State --}}
    @if($this->cartItems->isEmpty())
        <div class="text-center py-16 bg-white rounded-3xl shadow-card">
            <div class="w-16 h-16 bg-surface-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
            </div>
            <h3 class="font-heading text-lg font-bold text-surface-800 mb-1">Keranjang Kosong</h3>
            <p class="text-surface-500 text-sm max-w-sm mx-auto mb-6">Wah, keranjang belanjamu kosong. Yuk, cari produk menarik untuk dibeli!</p>
            <a href="{{ url('/products') }}" wire:navigate class="px-6 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-colors inline-block text-sm">Mulai Belanja</a>
        </div>
    @else
        <div class="grid lg:grid-cols-3 gap-8">
            {{-- Cart Items --}}
            <div class="lg:col-span-2 space-y-6">
                @foreach($this->groupedBySeller as $seller => $items)
                    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                        {{-- Seller Header with Select All --}}
                        <div class="px-5 py-3 bg-surface-50 border-b border-surface-100 flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox"
                                       wire:click="$toggle('isAllSelectedForSeller', '{{ $seller }}') ? selectAllForSeller('{{ $seller }}') : deselectAllForSeller('{{ $seller }}')"
                                       {{ $this->isAllSelectedForSeller($seller) ? 'checked' : '' }}
                                       wire:change="{{ $this->isAllSelectedForSeller($seller) ? 'deselectAllForSeller(\''.$seller.'\')' : 'selectAllForSeller(\''.$seller.'\')' }}"
                                       class="w-4 h-4 rounded border-surface-300 text-primary-500 focus:ring-primary-400">
                            </label>
                            <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-xs font-bold text-primary-600">{{ strtoupper(substr($seller, 0, 1)) }}</span>
                            </div>
                            <span class="font-semibold text-sm text-surface-800">{{ $seller }}</span>
                        </div>

                        <div class="divide-y divide-surface-50">
                            @foreach($items as $item)
                                <div class="p-5 flex gap-4 {{ !$item->selected ? 'opacity-60' : '' }}">
                                    {{-- Checkbox per item --}}
                                    <div class="flex items-center shrink-0">
                                        <label class="cursor-pointer select-none">
                                            <input type="checkbox"
                                                   wire:change="toggleSelected({{ $item->id }})"
                                                   {{ $item->selected ? 'checked' : '' }}
                                                   class="w-4 h-4 rounded border-surface-300 text-primary-500 focus:ring-primary-400">
                                        </label>
                                    </div>

                                    <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center shrink-0 overflow-hidden">
                                        @if($item->product->image)
                                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-8 h-8 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ url('/products/'.$item->product->slug) }}" wire:navigate class="font-semibold text-surface-800 text-sm hover:text-primary-500 line-clamp-1">
                                            {{ $item->product->name }}
                                        </a>
                                        <p class="text-primary-500 font-bold mt-1">Rp {{ number_format($item->product->price, 0, ',', '.') }}</p>
                                        <div class="flex items-center justify-between mt-3">
                                            <div class="flex items-center border border-surface-300 rounded-lg overflow-hidden bg-white">
                                                <button wire:click="updateQty({{ $item->id }}, {{ $item->qty - 1 }})" class="px-2.5 py-1.5 hover:bg-surface-50 text-surface-600 text-sm">−</button>
                                                <span class="w-10 text-center py-1.5 text-sm font-semibold">{{ $item->qty }}</span>
                                                <button wire:click="updateQty({{ $item->id }}, {{ $item->qty + 1 }})" class="px-2.5 py-1.5 hover:bg-surface-50 text-surface-600 text-sm">+</button>
                                            </div>
                                            <button wire:click="removeItem({{ $item->id }})" wire:confirm="Yakin ingin menghapus produk ini dari keranjang?" class="text-sm text-red-500 hover:text-red-600 font-medium transition-colors">Hapus</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Order Summary --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-card p-5 sticky top-24">
                    <h3 class="font-heading font-bold text-surface-900 mb-4">Ringkasan Belanja</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between text-surface-600">
                            <span>Total Harga ({{ $this->selectedCount }} barang dipilih)</span>
                            <span>Rp {{ number_format($this->selectedTotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-surface-600">
                            <span>Total Ongkir</span>
                            <span class="text-surface-400">Dihitung saat checkout</span>
                        </div>
                        <hr class="border-surface-100">
                        <div class="flex justify-between font-bold text-surface-900 text-base">
                            <span>Total</span>
                            <span class="text-primary-500">Rp {{ number_format($this->selectedTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <button wire:click="checkout"
                            class="mt-5 w-full flex items-center justify-center gap-2 py-3 text-white font-bold rounded-xl transition-all text-sm {{ $this->hasSelected ? 'bg-primary-500 hover:bg-primary-600 hover:shadow-lg' : 'bg-surface-400 cursor-not-allowed pointer-events-none' }}">
                        Checkout ({{ $this->selectedCount }} Barang)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>

                    @if(!$this->hasSelected)
                        <p class="text-xs text-amber-600 text-center mt-3">Centang produk yang ingin dicheckout terlebih dahulu.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
