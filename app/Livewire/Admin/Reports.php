<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
class Reports extends Component
{
    public string $startDate = '';
    public string $endDate = '';

    public function updatedStartDate(): void
    {
        $this->resetValidation();
    }

    public function updatedEndDate(): void
    {
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        [$start, $end, $isValid] = $this->dateRange();

        $storeQuery = Store::query()->where('status', 'approved');
        $orderQuery = Order::query()->whereIn('status', ['processing', 'shipped', 'delivered']);

        if ($isValid) {
            $this->applyDateRange($storeQuery, $start, $end);
            $this->applyDateRange($orderQuery, $start, $end);
        } else {
            $storeQuery->whereRaw('1 = 0');
            $orderQuery->whereRaw('1 = 0');
        }

        $totalSellers = Store::query()->where('status', 'approved')->count();
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

        $orders = (clone $orderQuery)->orderBy('created_at')->get();
        $stores = (clone $storeQuery)->orderBy('created_at')->get();
        $revenueChart = $this->buildChart($orders, $start, $end, 'total_price', 'Tren Omzet');
        $sellerChart = $this->buildChart($stores, $start, $end, null, 'Tren Seller Baru');

        return view('livewire.admin.reports', [
            'totalSellers' => $totalSellers,
            'newSellersCount' => $newSellersCount,
            'transactionsCount' => $transactionsCount,
            'totalRevenue' => $totalRevenue,
            'topSellers' => $topSellers,
            'revenueChartLabels' => $revenueChart['labels'],
            'revenueChartValues' => $revenueChart['values'],
            'revenueChartHasData' => $revenueChart['hasData'],
            'revenueChartTitle' => $revenueChart['title'],
            'sellerChartLabels' => $sellerChart['labels'],
            'sellerChartValues' => $sellerChart['values'],
            'sellerChartHasData' => $sellerChart['hasData'],
            'sellerChartTitle' => $sellerChart['title'],
            'periodLabel' => $this->periodLabel($start, $end),
            'pdfQuery' => $this->pdfQuery(),
        ]);
    }

    private function dateRange(): array
    {
        $this->resetValidation();

        $validator = Validator::make([
            'startDate' => $this->startDate ?: null,
            'endDate' => $this->endDate ?: null,
        ], [
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
        ], [
            'endDate.after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        if ($validator->fails()) {
            $this->setErrorBag($validator->errors());
            return [null, null, false];
        }

        $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : null;
        $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : null;

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

    private function pdfQuery(): array
    {
        return array_filter([
            'start_date' => $this->startDate ?: null,
            'end_date' => $this->endDate ?: null,
        ]);
    }

    private function buildChart(Collection $items, ?Carbon $start, ?Carbon $end, ?string $sumField, string $title): array
    {
        if ($items->isEmpty()) {
            return [
                'labels' => [],
                'values' => [],
                'hasData' => false,
                'title' => $title,
            ];
        }

        $isDaily = $start && $end && $start->diffInDays($end) <= 31;
        $labels = [];
        $values = [];

        if ($isDaily) {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $key = $date->format('Y-m-d');
                $labels[$key] = $date->translatedFormat('d M');
                $values[$key] = 0;
            }

            foreach ($items as $item) {
                $key = $item->created_at->format('Y-m-d');
                $labels[$key] = $labels[$key] ?? $item->created_at->translatedFormat('d M');
                $values[$key] = ($values[$key] ?? 0) + ($sumField ? (int) $item->{$sumField} : 1);
            }

            ksort($labels);
            ksort($values);

            return [
                'labels' => array_values($labels),
                'values' => array_values($values),
                'hasData' => array_sum($values) > 0,
                'title' => $title . ' Harian',
            ];
        }

        foreach ($items->sortBy('created_at') as $item) {
            $key = $item->created_at->format('Y-m');
            $labels[$key] = $item->created_at->translatedFormat('M Y');
            $values[$key] = ($values[$key] ?? 0) + ($sumField ? (int) $item->{$sumField} : 1);
        }

        return [
            'labels' => array_values($labels),
            'values' => array_values($values),
            'hasData' => array_sum($values) > 0,
            'title' => $title . ' Bulanan',
        ];
    }
}
