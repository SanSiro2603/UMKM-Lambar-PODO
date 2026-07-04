<div>
    @if($store->status !== 'approved')
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center shadow-card max-w-2xl mx-auto my-12">
            <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-surface-900 mb-2">Toko Anda Sedang Ditinjau</h3>
            <p class="text-surface-600 text-sm mb-4">
                Toko <strong>{{ $store->name }}</strong> saat ini berstatus 
                <span class="font-semibold uppercase text-amber-600">
                    {{ $store->status === 'pending' ? 'Menunggu Persetujuan' : 'Ditolak' }}
                </span>. 
                Anda belum dapat mengelola produk dan menerima pesanan hingga admin menyetujui toko Anda.
            </p>
            @if($store->status === 'rejected')
                <div class="p-3 bg-red-50 text-red-700 rounded-xl text-sm font-semibold max-w-md mx-auto">
                    Pendaftaran Toko Ditolak. Silakan hubungi admin untuk informasi lebih lanjut.
                </div>
            @else
                <p class="text-surface-500 text-xs">Proses verifikasi dokumen pendaftaran toko biasanya memakan waktu 1-2 hari kerja.</p>
            @endif
        </div>
    @else
        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card label="Total Produk" value="{{ $totalProducts }}" icon="box" color="primary" />
            <x-stat-card label="Pesanan Baru" value="{{ $newOrders }}" icon="cart" color="blue" />
            <x-stat-card label="Pendapatan Toko" value="Rp {{ number_format($revenue, 0, ',', '.') }}" icon="money" color="green" />
            <x-stat-card label="Total Pelanggan" value="{{ $totalCustomers }}" icon="users" color="accent" />
        </div>

        {{-- Split Payment Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-2xl shadow-card p-5 border-l-4 border-green-500">
                <p class="text-xs text-surface-500 font-medium uppercase tracking-wider">Sudah Dicairkan (Ke Rekening Anda)</p>
                <p class="text-xl font-bold text-green-600 mt-1">Rp {{ number_format($totalSalesSplit, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-card p-5 border-l-4 border-amber-500">
                <p class="text-xs text-surface-500 font-medium uppercase tracking-wider">Menunggu Pencairan</p>
                <p class="text-xl font-bold text-amber-600 mt-1">Rp {{ number_format($pendingDisbursement, 0, ',', '.') }}</p>
                <p class="text-[10px] text-surface-400 mt-1">Sudah dibayar customer, sedang diproses</p>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-6">
            {{-- Sales Chart --}}
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-card p-5" wire:ignore>
                <h3 class="font-heading font-bold text-surface-900 mb-4">Tren Omzet Penjualan (7 Hari Terakhir)</h3>
                <div id="sales-chart" class="w-full"></div>
            </div>

            {{-- Status Chart --}}
            <div class="bg-white rounded-2xl shadow-card p-5" wire:ignore>
                <h3 class="font-heading font-bold text-surface-900 mb-4">Status Pesanan Toko</h3>
                @if(empty($donutValues))
                    <div class="flex flex-col items-center justify-center h-[280px] text-surface-400">
                        <svg class="w-12 h-12 mb-2 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <p class="text-xs font-semibold">Belum ada pesanan masuk</p>
                    </div>
                @else
                    <div id="status-chart" class="w-full"></div>
                @endif
            </div>
        </div>

        {{-- Sales Dashboard Insights --}}
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-card p-5" wire:ignore>
                <h3 class="font-heading font-bold text-surface-900 mb-1">Produk Terlaris</h3>
                <p class="text-xs text-surface-500 mb-4">Top 5 produk berdasarkan jumlah terjual</p>
                @if(empty($topProductValues))
                    <div class="flex flex-col items-center justify-center h-[280px] text-surface-400">
                        <svg class="w-12 h-12 mb-2 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V7a2 2 0 00-2-2h-3.172a2 2 0 01-1.414-.586l-.828-.828A2 2 0 0011.172 3H6a2 2 0 00-2 2v8m16 0v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4m16 0H4"/></svg>
                        <p class="text-xs font-semibold">Belum ada produk terjual</p>
                    </div>
                @else
                    <div id="top-products-chart" class="w-full"></div>
                @endif
            </div>

            <div class="bg-white rounded-2xl shadow-card p-5" wire:ignore>
                <h3 class="font-heading font-bold text-surface-900 mb-1">Omzet Produk Selesai</h3>
                <p class="text-xs text-surface-500 mb-4">Top 5 produk penyumbang omzet</p>
                @if(empty($topRevenueProductValues))
                    <div class="flex flex-col items-center justify-center h-[280px] text-surface-400">
                        <svg class="w-12 h-12 mb-2 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 9v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs font-semibold">Belum ada omzet produk selesai</p>
                    </div>
                @else
                    <div id="top-revenue-products-chart" class="w-full"></div>
                @endif
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-2xl shadow-card p-5" wire:ignore>
                <h3 class="font-heading font-bold text-surface-900 mb-1">Metode Pembayaran</h3>
                <p class="text-xs text-surface-500 mb-4">Komposisi order Transfer dan COD</p>
                @if(empty($paymentMethodValues))
                    <div class="flex flex-col items-center justify-center h-[260px] text-surface-400">
                        <svg class="w-12 h-12 mb-2 text-surface-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <p class="text-xs font-semibold">Belum ada data pembayaran</p>
                    </div>
                @else
                    <div id="payment-method-chart" class="w-full"></div>
                @endif
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-card overflow-hidden">
                <div class="px-5 py-4 border-b border-surface-100">
                    <h3 class="font-heading font-bold text-surface-900">Stok Menipis</h3>
                    <p class="text-xs text-surface-500 mt-1">Produk dengan stok 5 atau kurang</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-surface-50 text-surface-600 font-semibold">
                            <tr>
                                <th class="px-5 py-3">Produk</th>
                                <th class="px-5 py-3 text-right">Sisa Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-50">
                            @forelse($lowStockProducts as $product)
                                <tr class="hover:bg-surface-50/50 transition-colors">
                                    <td class="px-5 py-4 font-semibold text-surface-800">{{ $product->name }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="inline-flex items-center justify-center min-w-10 px-2 py-1 rounded-full text-xs font-bold {{ $product->stock <= 0 ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600' }}">
                                            {{ $product->stock }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-5 py-8 text-center text-surface-500 font-medium">
                                        Tidak ada produk dengan stok menipis.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Split Payment History --}}
        <div class="bg-white rounded-2xl shadow-card overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-surface-100">
                <h3 class="font-heading font-bold text-surface-900">Riwayat Pembayaran & Pencairan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-600 font-semibold">
                        <tr>
                            <th class="px-5 py-3">Pesanan</th>
                            <th class="px-5 py-3 text-right">Total Bayar</th>
                            <th class="px-5 py-3 text-right">Fee Platform</th>
                            <th class="px-5 py-3 text-right">Dana Diterima</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-50 font-medium">
                        @forelse($splitTransactions as $trx)
                            <tr class="hover:bg-surface-50/50 transition-colors">
                                <td class="px-5 py-4 text-surface-800 font-semibold">#{{ $trx->order->order_code ?? 'N/A' }}</td>
                                <td class="px-5 py-4 text-right text-surface-800">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right text-red-500">-Rp {{ number_format($trx->platform_fee ?? 0, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right text-green-600 font-bold">Rp {{ number_format($trx->seller_amount ?? 0, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $trx->status === 'disbursed' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
                                        {{ $trx->status === 'disbursed' ? 'Sudah Dicairkan' : 'Menunggu Cair' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-surface-500 font-medium">
                                    Belum ada transaksi split payment. Dana akan otomatis dicairkan setelah customer membayar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Orders (Full Width) --}}
        <div class="bg-white rounded-2xl shadow-card overflow-hidden">
            <div class="px-5 py-4 border-b border-surface-100 flex items-center justify-between">
                <h3 class="font-heading font-bold text-surface-900">Pesanan Terbaru</h3>
                <a href="{{ url('/seller/orders') }}" wire:navigate class="text-sm font-semibold text-primary-500 hover:text-primary-600 transition-colors">Lihat Semua Pesanan</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-50 text-surface-600 font-semibold">
                        <tr>
                            <th class="px-5 py-3">Kode Order</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Pembayaran</th>
                            <th class="px-5 py-3">Total Belanja</th>
                            <th class="px-5 py-3">Tanggal Masuk</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-50">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-surface-50/50 transition-colors">
                                <td class="px-5 py-4 font-semibold text-surface-800">#{{ $order->order_code }}</td>
                                <td class="px-5 py-4 text-surface-600 font-medium">{{ $order->customer->name }}</td>
                                <td class="px-5 py-4">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $order->payment_method === 'xendit' ? 'bg-blue-50 text-blue-600' : 'bg-orange-50 text-orange-600' }}">
                                        {{ $order->payment_method === 'xendit' ? 'Bayar Online' : 'COD' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-bold text-surface-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-surface-500 font-medium">{{ $order->created_at->format('d F Y, H:i') }} WIB</td>
                                <td class="px-5 py-4"><x-status-badge :status="$order->status" /></td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ url('/seller/orders/' . $order->id) }}" wire:navigate class="text-primary-500 hover:underline font-bold">Kelola</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-surface-500 font-medium">
                                    Belum ada pesanan masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @push('scripts')
        <script>
            (function () {
                const salesChartEl = document.querySelector("#sales-chart");
                if (salesChartEl) {
                    salesChartEl.innerHTML = '';
                    var salesOptions = {
                        series: [{
                            name: 'Omzet Penjualan',
                            data: @json($chartSales)
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
                            categories: @json($chartDays),
                            labels: {
                                style: { colors: '#757575', fontFamily: 'Outfit' }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function (value) {
                                    return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                                },
                                style: { colors: '#757575', fontFamily: 'Outfit' }
                            }
                        },
                        grid: { borderColor: '#eeeeee' },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                                }
                            }
                        }
                    };
                    var salesChart = new ApexCharts(salesChartEl, salesOptions);
                    salesChart.render();
                }

                // donut chart
                @if(!empty($donutValues))
                    const statusChartEl = document.querySelector("#status-chart");
                    if (statusChartEl) {
                        statusChartEl.innerHTML = '';
                        var statusOptions = {
                            series: @json($donutValues),
                            chart: {
                                type: 'donut',
                                height: 280,
                            },
                            labels: @json($donutLabels),
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
                                                label: 'Total Pesanan',
                                                formatter: function (w) {
                                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        var statusChart = new ApexCharts(statusChartEl, statusOptions);
                        statusChart.render();
                    }
                @endif

                const topProductsChartEl = document.querySelector("#top-products-chart");
                if (topProductsChartEl) {
                    topProductsChartEl.innerHTML = '';
                    var topProductsOptions = {
                        series: [{
                            name: 'Terjual',
                            data: @json($topProductValues)
                        }],
                        chart: {
                            type: 'bar',
                            height: 280,
                            toolbar: { show: false }
                        },
                        colors: ['#D4A843'],
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 6,
                                barHeight: '58%'
                            }
                        },
                        dataLabels: { enabled: false },
                        xaxis: {
                            categories: @json($topProductLabels),
                            labels: {
                                formatter: function (value) {
                                    return new Intl.NumberFormat('id-ID').format(value);
                                },
                                style: { colors: '#757575', fontFamily: 'Outfit' }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#424242', fontFamily: 'Outfit' }
                            }
                        },
                        grid: { borderColor: '#eeeeee' },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return new Intl.NumberFormat('id-ID').format(val) + ' produk';
                                }
                            }
                        }
                    };
                    var topProductsChart = new ApexCharts(topProductsChartEl, topProductsOptions);
                    topProductsChart.render();
                }

                const topRevenueProductsChartEl = document.querySelector("#top-revenue-products-chart");
                if (topRevenueProductsChartEl) {
                    topRevenueProductsChartEl.innerHTML = '';
                    var topRevenueProductsOptions = {
                        series: [{
                            name: 'Omzet',
                            data: @json($topRevenueProductValues)
                        }],
                        chart: {
                            type: 'bar',
                            height: 280,
                            toolbar: { show: false }
                        },
                        colors: ['#1B4332'],
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 6,
                                barHeight: '58%'
                            }
                        },
                        dataLabels: { enabled: false },
                        xaxis: {
                            categories: @json($topRevenueProductLabels),
                            labels: {
                                formatter: function (value) {
                                    return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                                },
                                style: { colors: '#757575', fontFamily: 'Outfit' }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#424242', fontFamily: 'Outfit' }
                            }
                        },
                        grid: { borderColor: '#eeeeee' },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return "Rp " + new Intl.NumberFormat('id-ID').format(val);
                                }
                            }
                        }
                    };
                    var topRevenueProductsChart = new ApexCharts(topRevenueProductsChartEl, topRevenueProductsOptions);
                    topRevenueProductsChart.render();
                }

                @if(!empty($paymentMethodValues))
                    const paymentMethodChartEl = document.querySelector("#payment-method-chart");
                    if (paymentMethodChartEl) {
                        paymentMethodChartEl.innerHTML = '';
                        var paymentMethodOptions = {
                            series: @json($paymentMethodValues),
                            chart: {
                                type: 'donut',
                                height: 260,
                            },
                            labels: @json($paymentMethodLabels),
                            colors: ['#3b82f6', '#f59e0b'],
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
                                                label: 'Total Order',
                                                formatter: function (w) {
                                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        };
                        var paymentMethodChart = new ApexCharts(paymentMethodChartEl, paymentMethodOptions);
                        paymentMethodChart.render();
                    }
                @endif
            })();
        </script>
        @endpush
    @endif
</div>
