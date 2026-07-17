<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminReportController extends Controller
{
    public function downloadPdf(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        [$start, $end, $isValid] = $this->dateRange($request);

        $storeQuery = Store::where('status', 'approved');
        $orderQuery = Order::whereIn('status', ['processing', 'shipped', 'delivered']);

        if ($isValid) {
            $this->applyDateRange($storeQuery, $start, $end);
            $this->applyDateRange($orderQuery, $start, $end);
        } else {
            $storeQuery->whereRaw('1 = 0');
            $orderQuery->whereRaw('1 = 0');
        }

        $totalSellers = Store::where('status', 'approved')->count();
        $newSellersCount = (clone $storeQuery)->count();
        $transactionsCount = (clone $orderQuery)->count();
        $totalRevenue = (clone $orderQuery)->sum('total_price');

        $topSellersQuery = Order::select('store_id', DB::raw('SUM(total_price) as revenue'), DB::raw('COUNT(id) as transactions'))
            ->whereIn('status', ['processing', 'shipped', 'delivered']);

        if ($isValid) {
            $this->applyDateRange($topSellersQuery, $start, $end);
        } else {
            $topSellersQuery->whereRaw('1 = 0');
        }

        $topSellersData = $topSellersQuery
            ->groupBy('store_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $topSellers = [];
        foreach ($topSellersData as $data) {
            $store = Store::withCount('products')->find($data->store_id);
            if ($store) {
                $topSellers[] = [
                    'name' => $store->name,
                    'products' => $store->products_count,
                    'transactions' => $data->transactions,
                    'revenue' => $data->revenue,
                ];
            }
        }

        $periodLabel = $isValid ? $this->periodLabel($start, $end) : 'Rentang tanggal tidak valid';
        $fileSuffix = $isValid ? $this->fileSuffix($start, $end) : 'rentang-tidak-valid';

        $pdf = Pdf::loadView('admin.reports.pdf', compact('totalSellers', 'newSellersCount', 'transactionsCount', 'totalRevenue', 'topSellers', 'periodLabel'));

        return $pdf->download('laporan-platform-' . $fileSuffix . '.pdf');
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
