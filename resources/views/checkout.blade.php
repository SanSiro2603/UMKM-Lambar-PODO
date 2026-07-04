@extends('layouts.app')

@section('title', 'Checkout')
@section('authenticated', 'true')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ paymentMethod: { store1: 'transfer', store2: 'cod' } }">
    <h1 class="font-heading text-2xl font-bold text-surface-900 mb-6">Checkout</h1>

    <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            {{-- Shipping Address --}}
            <div class="bg-white rounded-2xl shadow-card p-5">
                <h3 class="font-heading font-bold text-surface-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Alamat Pengiriman
                </h3>
                <div class="p-4 border-2 border-primary-500 bg-primary-50/50 rounded-xl">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-surface-800">Ahmad Supardi <span class="text-xs font-normal text-surface-500 ml-2">0812-3456-7890</span></p>
                            <p class="text-sm text-surface-600 mt-1">Jl. Merdeka No. 45, RT 02/RW 05, Kabupaten Lampung Barat</p>
                        </div>
                        <button class="text-sm text-primary-500 font-medium hover:underline shrink-0">Ubah</button>
                    </div>
                </div>
            </div>

            {{-- Order Group 1: Toko Kopi Pak Adi --}}
            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-3 bg-surface-50 border-b border-surface-100 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-xs font-bold text-primary-600">T</span>
                    </div>
                    <span class="font-semibold text-sm text-surface-800">Toko Kopi Pak Adi</span>
                </div>

                <div class="p-5 space-y-4">
                    {{-- Items --}}
                    <div class="flex gap-3 items-center text-sm">
                        <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-surface-800 font-medium">Kopi Robusta Lampung Barat Premium</p>
                            <p class="text-surface-500">2 x Rp 75.000</p>
                        </div>
                        <p class="font-semibold text-surface-800">Rp 150.000</p>
                    </div>
                    <div class="flex gap-3 items-center text-sm">
                        <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-surface-800 font-medium">Kopi Liberika Lampung Barat</p>
                            <p class="text-surface-500">1 x Rp 95.000</p>
                        </div>
                        <p class="font-semibold text-surface-800">Rp 95.000</p>
                    </div>

                    <hr class="border-surface-100">

                    {{-- Payment Method --}}
                    <div>
                        <h4 class="text-sm font-semibold text-surface-700 mb-3">Metode Pembayaran</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_store1" value="transfer" x-model="paymentMethod.store1" class="peer hidden">
                                <div class="p-3 rounded-xl border-2 text-center text-sm font-medium transition-all
                                            peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-600
                                            border-surface-200 text-surface-600 hover:border-surface-300">
                                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    Transfer Langsung
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_store1" value="cod" x-model="paymentMethod.store1" class="peer hidden">
                                <div class="p-3 rounded-xl border-2 text-center text-sm font-medium transition-all
                                            peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-600
                                            border-surface-200 text-surface-600 hover:border-surface-300">
                                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    COD (Bayar di Tempat)
                                </div>
                            </label>
                        </div>

                        {{-- Bank Info (shown for transfer) --}}
                        <div x-show="paymentMethod.store1 === 'transfer'" x-transition class="mt-3 p-3 bg-accent-50 rounded-xl border border-accent-200">
                            <p class="text-xs font-semibold text-accent-700 mb-2">Informasi Rekening Penjual:</p>
                            <p class="text-sm text-surface-800"><span class="font-semibold">BRI</span> — 1234 5678 9012 3456</p>
                            <p class="text-sm text-surface-600">a.n. <span class="font-medium">Adi Suryanto</span></p>
                        </div>
                    </div>

                    <div class="flex justify-between text-sm font-semibold text-surface-800 pt-2">
                        <span>Subtotal</span>
                        <span>Rp 245.000</span>
                    </div>
                </div>
            </div>

            {{-- Order Group 2: Snack Nusantara --}}
            <div class="bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-3 bg-surface-50 border-b border-surface-100 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-xs font-bold text-primary-600">S</span>
                    </div>
                    <span class="font-semibold text-sm text-surface-800">Snack Nusantara</span>
                </div>

                <div class="p-5 space-y-4">
                    <div class="flex gap-3 items-center text-sm">
                        <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-surface-800 font-medium">Keripik Pisang Coklat Renyah</p>
                            <p class="text-surface-500">3 x Rp 25.000</p>
                        </div>
                        <p class="font-semibold text-surface-800">Rp 75.000</p>
                    </div>

                    <hr class="border-surface-100">

                    <div>
                        <h4 class="text-sm font-semibold text-surface-700 mb-3">Metode Pembayaran</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_store2" value="transfer" x-model="paymentMethod.store2" class="peer hidden">
                                <div class="p-3 rounded-xl border-2 text-center text-sm font-medium transition-all
                                            peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-600
                                            border-surface-200 text-surface-600 hover:border-surface-300">
                                    Transfer Langsung
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_store2" value="cod" x-model="paymentMethod.store2" class="peer hidden">
                                <div class="p-3 rounded-xl border-2 text-center text-sm font-medium transition-all
                                            peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-600
                                            border-surface-200 text-surface-600 hover:border-surface-300">
                                    COD (Bayar di Tempat)
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-between text-sm font-semibold text-surface-800 pt-2">
                        <span>Subtotal</span>
                        <span>Rp 75.000</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl shadow-card p-5 sticky top-24">
                <h3 class="font-heading font-bold text-surface-900 mb-4">Ringkasan Pesanan</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-surface-600">
                        <span>Subtotal Produk</span>
                        <span>Rp 320.000</span>
                    </div>
                    <div class="flex justify-between text-surface-600">
                        <span>Total Ongkir (2 toko)</span>
                        <span>Rp 30.000</span>
                    </div>
                    <hr class="border-surface-100">
                    <div class="flex justify-between font-bold text-surface-900 text-lg">
                        <span>Total Bayar</span>
                        <span class="text-primary-500">Rp 350.000</span>
                    </div>
                </div>

                <button class="mt-5 w-full flex items-center justify-center gap-2 py-3 bg-primary-500 text-white font-bold rounded-xl hover:bg-primary-600 transition-all hover:shadow-lg text-sm">
                    Konfirmasi Pesanan
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </button>

                <p class="text-xs text-surface-400 text-center mt-3">Dengan mengkonfirmasi, Anda menyetujui Syarat & Ketentuan yang berlaku.</p>
            </div>
        </div>
    </div>
</div>
@endsection
