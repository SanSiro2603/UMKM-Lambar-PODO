<a href="{{ url('/cart') }}" wire:navigate
   aria-label="Keranjang, {{ $cartCount }} item"
   class="relative min-w-11 min-h-11 rounded-xl hover:bg-surface-100 transition-colors inline-flex items-center justify-center"
   x-data="{ bouncing: false }"
   x-on:cart-badge-bounce.window="bouncing = true; setTimeout(() => bouncing = false, 600)">
    <svg aria-hidden="true" class="w-5 h-5 text-surface-600" :class="bouncing && 'cart-pop'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
    </svg>
    @if($cartCount > 0)
        <span :class="bouncing && 'cart-pop'"
              class="absolute -top-0.5 -right-0.5 w-5 h-5 bg-danger text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
            {{ $cartCount }}
        </span>
    @endif
</a>
