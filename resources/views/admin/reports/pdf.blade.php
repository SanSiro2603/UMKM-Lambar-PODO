<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kinerja Platform — UMKM Lampung Barat</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; font-size: 11px; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #1B4332; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #1B4332; }
        .header p { margin: 5px 0 0 0; color: #666; font-size: 12px; }
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { padding: 4px 0; vertical-align: top; }
        .meta-label { font-weight: bold; width: 120px; color: #555; }
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
        <h1>Laporan Kinerja Platform UMKM Lampung Barat</h1>
        <p>Dashboard Administrasi Global</p>
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Periode Analisis</td>
            <td>: {{ $periodLabel }}</td>
            <td class="meta-label">Total Omzet Platform</td>
            <td>: <strong>Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="meta-label">Tanggal Cetak</td>
            <td>: {{ now()->format('d F Y, H:i') }} WIB</td>
            <td class="meta-label">Volume Transaksi</td>
            <td>: {{ $transactionsCount }} Transaksi Selesai</td>
        </tr>
        <tr>
            <td class="meta-label">Seller Baru Terdaftar</td>
            <td>: {{ $newSellersCount }} Seller</td>
            <td class="meta-label">Total Seller Aktif</td>
            <td>: {{ $totalSellers }} Toko</td>
        </tr>
    </table>

    <h3 style="margin-top: 25px; color: #1B4332;">Top Seller (Urutan Omzet Terbesar)</h3>
    <table class="sales-table">
        <thead>
            <tr>
                <th style="width: 10%;">Urutan</th>
                <th style="width: 35%;">Nama Toko</th>
                <th style="width: 15%;">Jumlah Produk</th>
                <th style="width: 20%;">Total Transaksi</th>
                <th style="width: 20%;" class="right">Total Omzet</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topSellers as $i => $seller)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td><strong>{{ $seller['name'] }}</strong></td>
                    <td>{{ $seller['products'] }} produk</td>
                    <td>{{ $seller['transactions'] }} kali</td>
                    <td class="right">Rp {{ number_format($seller['revenue'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #999;">Tidak ada data transaksi penjualan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
