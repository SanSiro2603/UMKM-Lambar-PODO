<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10"
     x-data @open-whatsapp.window="window.open($event.detail.url, '_blank', 'noopener,noreferrer')">

    {{-- Store Header --}}
    <section class="bg-white rounded-2xl overflow-hidden shadow-card mb-8">
        {{-- Banner --}}
        <div class="relative h-40 sm:h-56 overflow-hidden bg-surface-100">
            @if($store->banner)
                <img src="{{ asset('storage/' . $store->banner) }}" alt="{{ $store->name }} Banner" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-primary-900/35 via-primary-900/5 to-transparent"></div>
            @else
                <div class="absolute inset-0 hero-gradient"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.18),transparent_28%),radial-gradient(circle_at_82%_18%,rgba(212,168,67,0.24),transparent_26%)]"></div>
            @endif
        </div>

        {{-- Store Info --}}
        <div class="relative px-5 sm:px-7 lg:px-8 pb-6 sm:pb-7">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between -mt-12 sm:-mt-14">
                <div class="flex flex-col sm:flex-row sm:items-end gap-4 min-w-0">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white shadow-card flex items-center justify-center border-4 border-white shrink-0 overflow-hidden">
                        @if($store->logo)
                            <img src="{{ asset('storage/' . $store->logo) }}" alt="{{ $store->name }} Logo" class="w-full h-full object-cover">
                        @else
                            <span class="font-heading font-bold text-4xl text-primary-500">{{ strtoupper(substr($store->name, 0, 1)) }}</span>
                        @endif
                    </div>

                    <div class="min-w-0 sm:pb-2">
                        <h1 class="font-heading text-2xl sm:text-3xl font-bold text-surface-900">{{ $store->name }}</h1>
                        <p class="text-surface-500 text-sm sm:text-base leading-relaxed mt-2 max-w-3xl">
                            {{ $store->description ?? 'Tidak ada deskripsi toko.' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 shrink-0 w-full sm:w-auto">
                    @if($whatsappAvailable)
                        <button type="button" wire:click="openContactModal" wire:loading.attr="disabled" wire:target="openContactModal"
                                class="min-h-11 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-[#25D366] rounded-xl hover:bg-[#1EAE54] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed transition-colors motion-reduce:transition-none cursor-pointer">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479s1.065 2.875 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.895 6.994c-.002 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.14 1.588 5.945L.057 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            <span wire:loading.remove wire:target="openContactModal">Hubungi Seller</span>
                            <span wire:loading wire:target="openContactModal">Membuka...</span>
                        </button>
                    @else
                        <button type="button" disabled aria-describedby="whatsapp-unavailable"
                                class="min-h-11 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-surface-500 bg-surface-100 border border-surface-200 rounded-xl cursor-not-allowed">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479s1.065 2.875 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.895 6.994c-.002 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.14 1.588 5.945L.057 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            WhatsApp Tidak Tersedia
                        </button>
                        <span id="whatsapp-unavailable" class="text-xs text-surface-500 text-center sm:text-left">Nomor WhatsApp toko belum tersedia.</span>
                    @endif

                    <button
                        type="button"
                        x-data
                        @click="navigator.clipboard?.writeText(window.location.href); window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Link toko berhasil disalin.', type: 'success' } }))"
                        class="min-h-11 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-primary-500 border border-primary-500 rounded-xl hover:bg-primary-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 focus-visible:ring-offset-2 transition-colors motion-reduce:transition-none cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                        Bagikan
                    </button>
                </div>
            </div>

            {{-- Stats --}}
            <div class="mt-6 pt-5 border-t border-surface-100 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="flex items-start gap-3 text-sm text-surface-500 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <span class="pt-1.5"><span class="font-bold text-surface-800">{{ $products->count() }}</span> Produk</span>
                </div>
                <div class="flex items-start gap-3 text-sm text-surface-500 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-yellow-50 text-yellow-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span class="pt-1.5">
                        @if($store->rating_count > 0)
                            <span class="font-bold text-surface-800">{{ number_format($store->avg_rating, 1) }}</span>
                            <span class="text-surface-400"> ({{ $store->rating_count }} ulasan)</span>
                        @else
                            Belum ada rating
                        @endif
                    </span>
                </div>
                <div class="flex items-start gap-3 text-sm text-surface-500 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <span class="pt-1.5 leading-relaxed line-clamp-2">{{ $store->address ?? 'Kabupaten Lampung Barat' }}</span>
                </div>
                <div class="flex items-start gap-3 text-sm text-surface-500 min-w-0">
                    <span class="w-9 h-9 rounded-xl bg-primary-50 text-primary-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <span class="pt-1.5">Bergabung sejak {{ $store->created_at->format('M Y') }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Products --}}
    <section>
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-5">
            <div>
                <h2 class="font-heading text-xl sm:text-2xl font-bold text-surface-900">Semua Produk</h2>
                <p class="text-sm text-surface-500 mt-1">{{ $products->count() }} produk tersedia dari {{ $store->name }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @forelse($products as $product)
                <div class="group bg-white rounded-2xl overflow-hidden shadow-card card-hover flex flex-col h-full">
                    {{-- Image --}}
                    <a href="{{ url('/products/' . $product->slug) }}" wire:navigate class="block relative overflow-hidden aspect-square shrink-0">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center">
                                <svg class="w-12 h-12 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif
                        @if($product->category)
                            <span class="absolute top-3 left-3 max-w-[calc(100%-1.5rem)] px-2.5 py-1 bg-white/90 backdrop-blur-sm rounded-full text-xs font-semibold text-primary-600 truncate">{{ $product->category->name }}</span>
                        @endif
                    </a>

                    {{-- Info --}}
                    <div class="p-4 flex-1 flex flex-col">
                        <a href="{{ url('/products/' . $product->slug) }}" wire:navigate class="block">
                            <h3 class="font-semibold text-surface-800 text-sm line-clamp-2 group-hover:text-primary-500 transition-colors">{{ $product->name }}</h3>
                        </a>
                        <p class="text-primary-500 font-bold text-base sm:text-lg mt-2">Rp {{ number_format($product->price, 0, ',', '.') }}</p>

                        <div class="mt-auto pt-3 flex items-center justify-between gap-1">
                            @php $avg = round($product->ratings_avg_rating ?? 0, 1); $cnt = $product->ratings_count ?? 0; @endphp
                            @if($cnt > 0)
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-yellow-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-xs font-semibold text-surface-700">{{ number_format($avg, 1) }}</span>
                                    <span class="text-xs text-surface-400">({{ $cnt }})</span>
                                </div>
                            @else
                                <span class="text-[11px] text-surface-400 italic">Belum ada ulasan</span>
                            @endif
                            <span class="text-xs text-surface-500">{{ $product->sold_quantity }} terjual</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-2xl shadow-card px-6 py-14 text-center">
                    <div class="w-16 h-16 bg-primary-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <h3 class="font-heading text-lg font-bold text-surface-900">Belum Ada Produk</h3>
                    <p class="text-sm text-surface-500 mt-1">Toko ini belum menambahkan produk.</p>
                </div>
            @endforelse
        </div>
    </section>

    @if($showContactModal)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="contact-dialog-title"
             x-data x-init="$nextTick(() => $refs.firstTopic.focus())" @keydown.escape.window="$wire.closeContactModal()">
            <button type="button" aria-label="Tutup dialog kontak seller" wire:click="closeContactModal" class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm cursor-default"></button>
            <div class="relative w-full max-w-xl max-h-[calc(100vh-2rem)] overflow-y-auto rounded-2xl bg-white shadow-2xl border border-surface-200 p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <span class="w-11 h-11 rounded-xl bg-[#E9FBEF] text-[#128C4A] flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479s1.065 2.875 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.895 6.994c-.002 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.14 1.588 5.945L.057 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        </span>
                        <div>
                            <h2 id="contact-dialog-title" class="font-heading text-lg font-bold text-surface-900">Hubungi {{ $store->name }}</h2>
                            <p class="text-sm text-surface-600 mt-1">Pilih topik agar seller langsung memahami kebutuhan Anda.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeContactModal" aria-label="Tutup" class="min-w-11 min-h-11 -mr-2 -mt-2 inline-flex items-center justify-center rounded-xl text-surface-500 hover:bg-surface-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit="contactSeller" class="mt-5 space-y-5">
                    <fieldset>
                        <legend class="text-sm font-semibold text-surface-800 mb-2">Apa yang ingin Anda tanyakan?</legend>
                        <div class="grid sm:grid-cols-2 gap-2">
                            @foreach($contactTopics as $key => $label)
                                <label class="min-h-11 flex items-center gap-3 rounded-xl border px-3 py-2.5 cursor-pointer transition-colors motion-reduce:transition-none {{ $contactTopic === $key ? 'border-[#25D366] bg-[#F0FDF4]' : 'border-surface-200 hover:border-surface-300 hover:bg-surface-50' }} {{ $key === 'complaint' ? 'sm:col-span-2' : '' }}">
                                    <input type="radio" name="contact-topic" value="{{ $key }}" wire:model.live="contactTopic" @if($loop->first) x-ref="firstTopic" @endif
                                           class="w-4 h-4 text-[#25D366] border-surface-300 focus:ring-[#25D366]">
                                    <span class="text-sm font-medium text-surface-800">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('contactTopic') <p role="alert" class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    </fieldset>

                    <div>
                        <label for="contact-note" class="block text-sm font-semibold text-surface-800 mb-1.5">Catatan tambahan <span class="font-normal text-surface-500">(opsional)</span></label>
                        <textarea id="contact-note" wire:model.live.debounce.250ms="contactNote" rows="4" maxlength="500"
                                  placeholder="Tuliskan nama produk, jumlah, atau detail lain yang ingin ditanyakan."
                                  class="w-full rounded-xl border border-surface-300 px-4 py-3 text-sm focus:border-[#25D366] focus:ring-2 focus:ring-green-100"
                                  aria-describedby="contact-note-help contact-note-error"></textarea>
                        <div id="contact-note-help" class="mt-1 flex justify-between gap-3 text-xs text-surface-500">
                            <span>{{ $contactTopic === 'complaint' ? 'Untuk komplain, sertakan nomor pesanan bila ada.' : 'Pesan ini akan ditambahkan ke template WhatsApp.' }}</span>
                            <span>{{ mb_strlen($contactNote) }}/500</span>
                        </div>
                        @error('contactNote') <p id="contact-note-error" role="alert" class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        @error('whatsapp') <p role="alert" class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <button type="button" wire:click="closeContactModal" class="min-h-11 px-4 py-2.5 rounded-xl border border-surface-300 text-sm font-semibold text-surface-700 hover:bg-surface-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 cursor-pointer">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="contactSeller"
                                class="min-h-11 inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#25D366] text-white text-sm font-bold hover:bg-[#1EAE54] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
                            <svg wire:loading.remove wire:target="contactSeller" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479s1.065 2.875 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.981.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.895 6.994c-.002 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.14 1.588 5.945L.057 24l6.304-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            <span wire:loading.remove wire:target="contactSeller">Lanjut ke WhatsApp</span>
                            <span wire:loading wire:target="contactSeller">Menyiapkan Pesan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
