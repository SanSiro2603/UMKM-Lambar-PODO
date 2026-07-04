<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SellerReportController extends Controller
{
    public function downloadPdf(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller' || !$user->store) {
            abort(403);
        }

        $store = $user->store;
        if ($store->status !== 'approved') {
            abort(403, 'Akses ditolak. Toko Anda belum disetujui.');
        }
        [$start, $end, $isValid] = $this->dateRange($request);

        $query = Order::with(['customer', 'items.product'])
            ->where('store_id', $store->id)
            ->whereIn('status', ['paid', 'shipped', 'delivered']);

        if ($isValid) {
            $this->applyDateRange($query, $start, $end);
        } else {
            $query->whereRaw('1 = 0');
        }

        $sales = $query->orderBy('created_at', 'desc')->get();
        $totalSales = $sales->sum('total_price');
        $completedOrdersCount = $sales->count();
        $periodLabel = $isValid ? $this->periodLabel($start, $end) : 'Rentang tanggal tidak valid';
        $fileSuffix = $isValid ? $this->fileSuffix($start, $end) : 'rentang-tidak-valid';

        $pdf = Pdf::loadView('seller.reports.pdf', compact('store', 'sales', 'totalSales', 'completedOrdersCount', 'periodLabel'));

        return $pdf->download('laporan-penjualan-' . $store->slug . '-' . $fileSuffix . '.pdf');
    }

    private function dateRange(Request $request): array
    {
        $validator = Validator::make([
            'start_date' => $request->query('start_date') ?: null,
            'end_date' => $request->query('end_date') ?: null,
        ], [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        if ($validator->fails()) {
            return [null, null, false];
        }

        $start = $request->query('start_date') ? Carbon::parse($request->query('start_date'))->startOfDay() : null;
        $end = $request->query('end_date') ? Carbon::parse($request->query('end_date'))->endOfDay() : null;

        return [$start, $end, true];
    }

    private function applyDateRange(Builder $query, ?Carbon $start, ?Carbon $end): void
    {
        if ($start) {
            $query->where('created_at', '>=', $start);
        }

        if ($end) {
            $query->where('created_at', '<=', $end);
        }
    }

    private function periodLabel(?Carbon $start, ?Carbon $end): string
    {
        if (!$start && !$end) {
            return 'Semua Data';
        }

        if ($start && !$end) {
            return 'Mulai ' . $start->translatedFormat('d F Y');
        }

        if (!$start && $end) {
            return 'Sampai ' . $end->translatedFormat('d F Y');
        }

        return $start->translatedFormat('d F Y') . ' - ' . $end->translatedFormat('d F Y');
    }

    private function fileSuffix(?Carbon $start, ?Carbon $end): string
    {
        if (!$start && !$end) {
            return 'semua-data';
        }

        if ($start && !$end) {
            return 'mulai-' . $start->format('Y-m-d');
        }

        if (!$start && $end) {
            return 'sampai-' . $end->format('Y-m-d');
        }

        return $start->format('Y-m-d') . '-sampai-' . $end->format('Y-m-d');
    }
}
