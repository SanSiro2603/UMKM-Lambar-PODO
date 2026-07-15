<div class="relative" wire:poll.15s="refresh" x-data="{ open: @entangle('open') }" @click.away="$wire.close()">

    {{-- Bell Button --}}
    <button wire:click="toggle"
            class="relative p-2 rounded-lg hover:bg-surface-100 transition-colors focus:outline-none"
            aria-label="Notifikasi">
        <svg class="w-5 h-5 text-surface-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-0.5 flex items-center justify-center
                         bg-red-500 text-white text-[9px] font-bold rounded-full leading-none">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         class="absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-surface-200 z-50 overflow-hidden"
         style="display: none;">

        {{-- Header --}}
        <div class="flex items-center justify-between px-3 py-2.5 border-b border-surface-100">
            <span class="text-xs font-semibold text-surface-700 uppercase tracking-wide">Pesanan Masuk</span>
            @if($unreadCount > 0)
                <span class="text-[10px] font-bold px-2 py-0.5 bg-red-50 text-red-500 rounded-full">
                    {{ $unreadCount }} baru
                </span>
            @endif
        </div>

        {{-- List --}}
        <div class="max-h-72 overflow-y-auto">
            @forelse($notifications as $order)
                @php
                    [$dotBg, $statusText, $statusColor] = match($order->status) {
                        'waiting_payment' => ['bg-yellow-400', 'Menunggu Bayar',  'text-yellow-600'],
                        'paid'            => ['bg-blue-400',   'Siap Dikirim',    'text-blue-600'],
                        'shipped'         => ['bg-purple-400', 'Dikirim',         'text-purple-600'],
                        'delivered'       => ['bg-green-500',  'Selesai',         'text-green-600'],
                        'cancelled'       => ['bg-red-400',    'Dibatalkan',      'text-red-500'],
                        default           => ['bg-surface-300', $order->status,   'text-surface-500'],
                    };
                @endphp
                <a href="{{ route('seller.orders', $order->id) }}"
                   wire:navigate wire:click="close"
                   class="flex items-center gap-2.5 px-3 py-2.5 hover:bg-surface-50 transition-colors border-b border-surface-50 last:border-0
                          {{ $order->is_new ? 'bg-blue-50/50' : '' }}">

                    <span class="shrink-0 w-2 h-2 rounded-full mt-0.5 {{ $dotBg }}"></span>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <span class="text-xs font-semibold text-surface-800 truncate">{{ $order->order_code }}</span>
                            <span class="shrink-0 text-[10px] text-surface-400">{{ $order->created_at->diffForHumans(null, true) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-1 mt-0.5">
                            <span class="text-[10px] font-medium {{ $statusColor }}">{{ $statusText }}</span>
                            <span class="text-[10px] font-semibold text-surface-600">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    @if($order->is_new)
                        <span class="shrink-0 w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    @endif
                </a>
            @empty
                <div class="py-8 flex flex-col items-center text-surface-400">
                    <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-xs">Belum ada pesanan</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <a href="{{ route('seller.orders') }}" wire:navigate wire:click="close"
           class="flex items-center justify-center gap-1 py-2 text-xs font-medium text-primary-500
                  hover:text-primary-600 hover:bg-surface-50 border-t border-surface-100 transition-colors">
            Lihat semua pesanan
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</div>
