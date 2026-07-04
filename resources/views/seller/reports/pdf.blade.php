<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan — {{ $store->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; font-size: 11px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #1B4332; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #1B4332; }
        .header p { margin: 5px 0 0 0; color: #666; font-size: 12px; }
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { padding: 4px 0; vertical-align: top; }
        .meta-label { font-weight: bold; width: 100px; color: #555; }
        .sales-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .sales-table th, .sales-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .sales-table th { background-color: #1B4332; color: white; font-weight: bold; }
        .sales-table tr:nth-child(even) { background-color: #f9f9f9; }
        .total-row { font-weight: bold; background-color: #f1f1f1 !important; color: #1B4332; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Penjualan UMKM Lampung Barat</h1>
        <p>Nama Toko: <strong>{{ $store->name }}</strong></p>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Periode</td>
            <td>: {{ $periodLabel }}</td>
            <td class="meta-label">Total Penjualan</td>
            <td>: <strong>Rp {{ number_format($totalSales, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Cetak</td>
            <td>: {{ now()->format('d F Y, H:i') }} WIB</td>
            <td class="meta-label">Total Transaksi</td>
            <td>: {{ $completedOrdersCount }} Selesai</td>
        </tr>
    </table>

    <table class="sales-table">
        <thead>
            <tr>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 18%;">Kode Pesanan</th>
                <th style="width: 20%;">Pelanggan</th>
                <th style="width: 35%;">Produk & Qty</th>
                <th style="width: 15%;" class="right">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->created_at->format('d/m/Y') }}</td>
                    <td>#{{ $sale->order_code }}</td>
                    <td>{{ $sale->customer->name }}</td>
                    <td>
                        @foreach($sale->items as $item)
                            {{ $item->product->name }} (x{{ $item->qty }}){{ !$loop->last ? '; ' : '' }}
                        @endforeach
                    </td>
                    <td class="right">Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">Tidak ada data penjualan pada periode ini.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4">TOTAL PENDAPATAN</td>
                <td class="right">Rp {{ number_format($totalSales, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
