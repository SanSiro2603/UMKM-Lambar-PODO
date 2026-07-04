<div>
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Seller" value="{{ $totalSellers }}" icon="store" color="primary" />
        <x-stat-card label="Total Produk" value="{{ $totalProducts }}" icon="box" color="blue" />
        <x-stat-card label="Total Transaksi" value="{{ $totalOrders }}" icon="cart" color="green" />
        <x-stat-card label="Omzet Platform" value="Rp {{ number_format($totalOmzet, 0, ',', '.') }}" icon="money" color="accent" />
    </div>

    {{-- Split Payment Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-card p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 9v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-surface-500 font-medium">Pendapatan Platform</p>
                    <p class="text-lg font-bold text-primary-500">Rp {{ number_format($platformRevenue, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-surface-400">5% fee dari setiap transaksi</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-card p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-surface-500 font-medium">Sudah Dicairkan ke Seller</p>
                    <p class="text-lg font-bold text-blue-600">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-surface-400">Dana sudah dikirim ke rekening seller</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-card p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-surface-500 font-medium">Menunggu Disbursement</p>
                    <p class="text-lg font-bold text-amber-600">Rp {{ number_format($pendingDisbursement, 0, ',', '.') }}</p>
                    <p class="text-[10px] text-surface-400">Sudah dibayar customer, belum dicairkan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Growth Chart --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-card p-5" wire:ignore>
            <h3 class="font-heading font-bold text-surface-900 mb-4">Volume Transaksi Per Bulan (Tahun {{ now()->year }})</h3>
            <div id="growth-chart" class="w-full"></div>
        </div>

        {{-- Category Pie Chart --}}
        <div class="bg-white rounded-2xl shadow-card p-5" wire:ignore>
            <h3 class="font-heading font-bold text-surface-900 mb-4">Porsi Kategori Terlaris</h3>
            @if(empty($categoryValues))
                <div class="flex flex-col items-center justify-center h-[280px] text-surface-400">
                    <svg class="w-12 h-12 mb-2 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="text-xs font-semibold">Belum ada data penjualan selesai</p>
                </div>
            @else
                <div id="category-chart" class="w-full"></div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Split Payment Transactions --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-card overflow-hidden">
            <div class="px-5 py-4 border-b border-surface-100">
                <h3 class="font-heading font-bold text-surface-900">Transaksi Pembayaran (Terbaru)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-600 font-semibold">
                        <tr>
                            <th class="px-5 py-3">Pesanan</th>
                            <th class="px-5 py-3">Toko</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Fee Platform</th>
                            <th class="px-5 py-3 text-right">Ke Seller</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-50 font-medium">
                        @forelse($recentTransactions as $trx)
                            <tr class="hover:bg-surface-50/50 transition-colors">
                                <td class="px-5 py-4 text-surface-800 font-semibold">#{{ $trx->order->order_code ?? 'N/A' }}</td>
                                <td class="px-5 py-4 text-surface-500">{{ $trx->order->store->name ?? 'N/A' }}</td>
                                <td class="px-5 py-4 text-right text-surface-800">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right text-green-600 font-medium">Rp {{ number_format($trx->platform_fee ?? 0, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right text-blue-600 font-semibold">Rp {{ number_format($trx->seller_amount ?? 0, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $trx->status === 'disbursed' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
                                        {{ $trx->status === 'disbursed' ? 'Sudah Dicairkan' : 'Menunggu Cair' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-surface-500 font-medium">
                                    Belum ada transaksi split payment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pending Sellers --}}
        <div class="bg-white rounded-2xl shadow-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-heading font-bold text-surface-900">Menunggu Verifikasi</h3>
                <a href="{{ url('/admin/sellers') }}" wire:navigate class="text-xs font-medium text-primary-500 hover:underline">Semua</a>
            </div>
            <div class="space-y-3">
                @forelse($pendingSellers as $seller)
                    <div class="p-4 rounded-xl bg-amber-50/50 border border-amber-100">
                        <p class="font-semibold text-sm text-surface-800">{{ $seller->name }}</p>
                        <p class="text-xs text-surface-500 mt-0.5">Pemilik: {{ $seller->user->name }} · {{ $seller->created_at->format('d M Y') }}</p>
                        <div class="flex gap-2 mt-3">
                            <button wire:click="approveSeller({{ $seller->id }})" class="px-3 py-1.5 text-xs font-bold text-white bg-green-500 rounded-lg hover:bg-green-600 transition-colors">
                                Approve
                            </button>
                            <button wire:click="rejectSeller({{ $seller->id }})" wire:confirm="Yakin ingin menolak pendaftaran toko ini?" class="px-3 py-1.5 text-xs font-bold text-red-500 bg-white border border-red-200 rounded-lg hover:bg-red-50 transition-colors">
                                Tolak
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-surface-500 text-sm">
                        Tidak ada pendaftaran toko baru yang perlu ditinjau.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            // growth chart
            const growthChartEl = document.querySelector("#growth-chart");
            if (growthChartEl) {
                growthChartEl.innerHTML = '';
                var growthOptions = {
                    series: [{
                        name: 'Volume Transaksi',
                        data: @json($chartOrders)
                    }],
                    chart: {
                        type: 'area',
                        height: 280,
                        toolbar: { show: false },
                        zoom: { enabled: false }
                    },
                    colors: ['#1B4332'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [50, 100, 100]
                        }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3, colors: ['#1B4332'] },
                    xaxis: {
                        categories: @json($chartMonths),
                        labels: {
                            style: { colors: '#757575', fontFamily: 'Outfit' }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (value) {
                                return new Intl.NumberFormat('id-ID').format(value) + " Transaksi";
                            },
                            style: { colors: '#757575', fontFamily: 'Outfit' }
                        }
                    },
                    grid: { borderColor: '#eeeeee' },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return new Intl.NumberFormat('id-ID').format(val) + " Transaksi";
                            }
                        }
                    }
                };
                var growthChart = new ApexCharts(growthChartEl, growthOptions);
                growthChart.render();
            }

            // category chart
            @if(!empty($categoryValues))
                const categoryChartEl = document.querySelector("#category-chart");
                if (categoryChartEl) {
                    categoryChartEl.innerHTML = '';
                    var categoryOptions = {
                        series: @json($categoryValues),
                        chart: {
                            type: 'donut',
                            height: 280,
                        },
                        labels: @json($categoryLabels),
                        colors: ['#1B4332', '#D4A843', '#3b82f6', '#22c55e', '#f59e0b', '#ef4444'],
                        legend: {
                            position: 'bottom',
                            fontFamily: 'Outfit',
                            labels: { colors: '#424242' }
                        },
                        dataLabels: {
                            enabled: true,
                            style: { fontFamily: 'Outfit' }
                        },
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'Total Terjual',
                                            formatter: function (w) {
                                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + " Unit"
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    };
                    var categoryChart = new ApexCharts(categoryChartEl, categoryOptions);
                    categoryChart.render();
                }
            @endif
        })();
    </script>
    @endpush
</div>
