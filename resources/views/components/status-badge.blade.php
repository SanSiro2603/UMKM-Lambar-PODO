@props([
    'status' => 'pending',
])

@php
    $styles = [
        'keranjang' => 'bg-surface-100 text-surface-600',
        'waiting_payment' => 'bg-amber-50 text-amber-700',
        'processing' => 'bg-blue-50 text-blue-700',
        'paid' => 'bg-green-50 text-green-700',
        'shipped' => 'bg-cyan-50 text-cyan-700',
        'delivered' => 'bg-green-50 text-green-700',
        'cancelled' => 'bg-red-50 text-red-700',
        'unpaid' => 'bg-amber-50 text-amber-700',
        'failed' => 'bg-red-50 text-red-700',
        'menunggu_pembayaran' => 'bg-amber-50 text-amber-700',
        'menunggu_validasi' => 'bg-orange-50 text-orange-700',
        'menunggu_konfirmasi' => 'bg-blue-50 text-blue-700',
        'diproses' => 'bg-indigo-50 text-indigo-700',
        'dikirim' => 'bg-cyan-50 text-cyan-700',
        'selesai' => 'bg-green-50 text-green-700',
        'dibatalkan' => 'bg-red-50 text-red-700',
        'pending' => 'bg-amber-50 text-amber-700',
        'approved' => 'bg-green-50 text-green-700',
        'rejected' => 'bg-red-50 text-red-700',
        'suspended' => 'bg-slate-100 text-slate-700',
    ];

    $labels = [
        'keranjang' => 'Keranjang',
        'waiting_payment' => 'Menunggu Pembayaran',
        'processing' => 'Siap Diproses',
        'paid' => 'Sudah Dibayar',
        'shipped' => 'Dikirim',
        'delivered' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'unpaid' => 'Belum Dibayar',
        'failed' => 'Gagal',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_validasi' => 'Menunggu Validasi',
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'pending' => 'Menunggu Persetujuan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'suspended' => 'Dinonaktifkan',
    ];

    $dotColors = [
        'keranjang' => 'bg-surface-400',
        'waiting_payment' => 'bg-amber-500',
        'processing' => 'bg-blue-500',
        'paid' => 'bg-green-500',
        'shipped' => 'bg-cyan-500',
        'delivered' => 'bg-green-500',
        'cancelled' => 'bg-red-500',
        'unpaid' => 'bg-amber-500',
        'failed' => 'bg-red-500',
        'menunggu_pembayaran' => 'bg-amber-500',
        'menunggu_validasi' => 'bg-orange-500',
        'menunggu_konfirmasi' => 'bg-blue-500',
        'diproses' => 'bg-indigo-500',
        'dikirim' => 'bg-cyan-500',
        'selesai' => 'bg-green-500',
        'dibatalkan' => 'bg-red-500',
        'pending' => 'bg-amber-500',
        'approved' => 'bg-green-500',
        'rejected' => 'bg-red-500',
        'suspended' => 'bg-slate-500',
    ];

    $style = $styles[$status] ?? $styles['pending'];
    $label = $labels[$status] ?? ucfirst($status);
    $dot = $dotColors[$status] ?? 'bg-surface-400';
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $style }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
    {{ $label }}
</span>
