<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        
        {{-- Status Card --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            
            {{-- Header with Icon --}}
            <div class="px-6 py-8 {{ $store->status === 'pending' ? 'bg-gradient-to-r from-yellow-400 to-yellow-500' : 'bg-gradient-to-r from-red-400 to-red-500' }}">
                <div class="flex items-center justify-center">
                    @if($store->status === 'pending')
                        {{-- Pending Icon --}}
                        <svg class="w-20 h-20 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @else
                        {{-- Rejected Icon --}}
                        <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
                
                <h1 class="mt-4 text-3xl font-bold text-white text-center">
                    @if($store->status === 'pending')
                        Toko Sedang Dalam Review
                    @else
                        Pendaftaran Toko Ditolak
                    @endif
                </h1>
            </div>
            
            {{-- Content --}}
            <div class="px-6 py-8">
                
                @if($store->status === 'pending')
                    {{-- Pending Message --}}
                    <div class="text-center mb-8">
                        <p class="text-lg text-gray-700 mb-4">
                            Terima kasih telah mendaftar sebagai seller di UMKM Lampung Barat!
                        </p>
                        <p class="text-gray-600 mb-2">
                            Toko Anda sedang dalam proses verifikasi oleh tim admin kami.
                        </p>
                        <p class="text-gray-600">
                            Proses ini biasanya memakan waktu <span class="font-semibold">1-3 hari kerja</span>.
                        </p>
                    </div>
                    
                    {{-- Info Card --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                        <h3 class="text-sm font-semibold text-blue-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Apa yang akan terjadi setelah disetujui?
                        </h3>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Toko Anda akan muncul di halaman depan marketplace
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Anda dapat menambahkan dan mengelola produk
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Mulai menerima dan memproses pesanan dari customer
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Akses penuh ke dashboard dan laporan penjualan
                            </li>
                        </ul>
                    </div>
                    
                @else
                    {{-- Rejected Message --}}
                    <div class="text-center mb-8">
                        <p class="text-lg text-gray-700 mb-4">
                            Maaf, pendaftaran toko Anda ditolak oleh admin.
                        </p>
                        
                        @if($store->rejection_reason)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                <h3 class="text-sm font-semibold text-red-800 mb-2">Alasan Penolakan:</h3>
                                <p class="text-sm text-red-700">{{ $store->rejection_reason }}</p>
                            </div>
                        @endif
                        
                        <p class="text-gray-600">
                            Anda dapat mendaftar ulang dengan data yang benar dan lengkap.
                        </p>
                    </div>
                    
                    {{-- Action Button for Rejected --}}
                    <div class="flex justify-center mb-6">
                        <a href="{{ route('register.seller') }}" 
                           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200 shadow-lg hover:shadow-xl">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Daftar Ulang Sebagai Seller
                        </a>
                    </div>
                @endif
                
                {{-- Store Info --}}
                <div class="border-t border-gray-200 pt-6 mb-6">
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">Informasi Toko Anda:</h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nama Toko:</span>
                            <span class="font-medium text-gray-900">{{ $store->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                {{ $store->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800' }}">
                                @if($store->status === 'pending')
                                    ⏳ Menunggu Verifikasi
                                @else
                                    ❌ Ditolak
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tanggal Daftar:</span>
                            <span class="font-medium text-gray-900">{{ $store->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
                
                {{-- Logout Button --}}
                <div class="flex justify-center pt-4">
                    <form action="{{ route('logout') }}" method="POST" class="w-full max-w-xs">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
                
            </div>
        </div>
        
        {{-- Footer Note --}}
        <div class="text-center text-sm text-gray-500">
            <p>Butuh bantuan? Hubungi admin di <a href="mailto:admin@umkmairhitam.com" class="text-blue-600 hover:text-blue-800 underline">admin@umkmairhitam.com</a></p>
        </div>
        
    </div>
</div>
