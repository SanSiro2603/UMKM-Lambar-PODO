<div>
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs font-semibold text-surface-500 uppercase tracking-wider mb-2">Periode Laporan: {{ $periodLabel }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1.5">Dari Tanggal</label>
                    <input type="date" wire:model.live="startDate"
                           class="w-full px-4 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none">
                    @error('startDate') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-surface-700 mb-1.5">Sampai Tanggal</label>
                    <input type="date" wire:model.live="endDate"
                           class="w-full px-4 py-2.5 bg-white border border-surface-300 rounded-xl text-sm focus:border-primary-400 focus:ring-2 focus:ring-primary-100 outline-none">
                    @error('endDate') <span class="text-xs text-red-600 block mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <a href="{{ route('admin.reports.pdf', $pdfQuery) }}" target="_blank"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-500 text-white font-semibold rounded-xl hover:bg-primary-600 transition-all text-sm shadow-md">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Cetak Laporan PDF
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Seller Aktif" value="{{ $totalSellers }}" icon="store" color="primary" />
        <x-stat-card label="Seller Baru Terdaftar" value="{{ $newSellersCount }}" icon="users" color="blue" />
        <x-stat-card label="Volume Transaksi" value="{{ $transactionsCount }}" icon="cart" color="green" />
        <x-stat-card label="Total Omzet Platform" value="Rp {{ number_format($totalRevenue, 0, ',', '.') }}" icon="money" color="accent" />
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-card p-5"
             wire:key="admin-revenue-chart-{{ md5(json_encode($revenueChartLabels) . json_encode($revenueChartValues)) }}">
            <h3 class="font-heading font-bold text-surface-900 mb-1">{{ $revenueChartTitle }}</h3>
            <p class="text-xs text-surface-500 mb-4">{{ $periodLabel }}</p>

            @if(!$revenueChartHasData)
                <div class="flex flex-col items-center justify-center h-[280px] text-surface-400">
                    <svg class="w-12 h-12 mb-2 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="text-xs font-semibold">Belum ada data pada rentang ini</p>
                </div>
            @else
                <div
                    x-data="reportTrendChart('Omzet Platform', 'currency', '#1B4332')"
                    x-init="render()"
                    data-labels='@json($revenueChartLabels)'
                    data-values='@json($revenueChartValues)'
                >
                    <div x-ref="chart" class="w-full"></div>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-card p-5"
             wire:key="admin-seller-chart-{{ md5(json_encode($sellerChartLabels) . json_encode($sellerChartValues)) }}">
            <h3 class="font-heading font-bold text-surface-900 mb-1">{{ $sellerChartTitle }}</h3>
            <p class="text-xs text-surface-500 mb-4">{{ $periodLabel }}</p>

            @if(!$sellerChartHasData)
                <div class="flex flex-col items-center justify-center h-[280px] text-surface-400">
                    <svg class="w-12 h-12 mb-2 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="text-xs font-semibold">Belum ada data pada rentang ini</p>
                </div>
            @else
                <div
                    x-data="reportTrendChart('Seller Baru', 'count', '#D4A843')"
                    x-init="render()"
                    data-labels='@json($sellerChartLabels)'
                    data-values='@json($sellerChartValues)'
                >
                    <div x-ref="chart" class="w-full"></div>
                </div>
            @endif
        </div>
    </div>

    {{-- Top Sellers Table --}}
    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-surface-100">
            <h3 class="font-heading font-bold text-surface-900">Top Seller (Urutan Omzet Tertinggi)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-50">
                    <tr>
                        <th class="px-5 py-3 font-semibold text-surface-600" style="width: 10%;">#</th>
                        <th class="px-5 py-3 font-semibold text-surface-600" style="width: 35%;">Nama Toko</th>
                        <th class="px-5 py-3 font-semibold text-surface-600" style="width: 15%;">Jumlah Produk</th>
                        <th class="px-5 py-3 font-semibold text-surface-600" style="width: 20%;">Total Transaksi</th>
                        <th class="px-5 py-3 font-semibold text-surface-600" style="width: 20%;">Total Omzet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-50">
                    @forelse($topSellers as $i => $seller)
                        <tr class="hover:bg-surface-50/50 transition-colors">
                            <td class="px-5 py-4">
                                <span class="w-6 h-6 inline-flex items-center justify-center rounded-full {{ $i < 3 ? 'bg-accent-100 text-accent-700 font-bold' : 'bg-surface-100 text-surface-600' }} text-xs">
                                    {{ $i + 1 }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-semibold text-surface-800">{{ $seller['name'] }}</td>
                            <td class="px-5 py-4 text-surface-600">{{ $seller['products'] }} produk</td>
                            <td class="px-5 py-4 text-surface-600">{{ $seller['transactions'] }} kali</td>
                            <td class="px-5 py-4 font-semibold text-green-600">Rp {{ number_format($seller['revenue'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-surface-500 font-medium">
                                Belum ada transaksi penjualan pada rentang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        window.reportTrendChart = window.reportTrendChart || function (seriesName, valueType, color) {
            return {
                chart: null,
                render() {
                    if (this.chart) this.chart.destroy();

                    const labels = JSON.parse(this.$el.dataset.labels || '[]');
                    const values = JSON.parse(this.$el.dataset.values || '[]');
                    const formatValue = function (value) {
                        const formatted = new Intl.NumberFormat('id-ID').format(value);
                        return valueType === 'currency' ? 'Rp ' + formatted : formatted + ' Seller';
                    };

                    this.chart = new ApexCharts(this.$refs.chart, {
                        series: [{ name: seriesName, data: values }],
                        chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false } },
                        colors: [color],
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
                        stroke: { curve: 'smooth', width: 3, colors: [color] },
                        markers: {
                            size: 0,
                            hover: { size: 5 }
                        },
                        xaxis: { categories: labels, labels: { style: { colors: '#757575', fontFamily: 'Outfit' } } },
                        yaxis: { labels: { formatter: formatValue, style: { colors: '#757575', fontFamily: 'Outfit' } } },
                        grid: { borderColor: '#eeeeee' },
                        tooltip: { y: { formatter: formatValue } }
                    });
                    this.chart.render();
                }
            };
        };
    </script>
</div>
