<?php

namespace App\Livewire\Seller;

use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
        $store = Auth::user()->store;
        if (!$store) {
            abort(403);
        }

        [$start, $end, $isValid] = $this->dateRange();

        $query = Order::with(['customer', 'items.product'])
            ->where('store_id', $store->id)
            ->whereIn('status', ['paid', 'shipped', 'delivered']);

        if ($isValid) {
            $this->applyDateRange($query, $start, $end);
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {
            $sales = collect();
        }

        $totalSales = $sales->sum('total_price');
        $completedOrdersCount = $sales->count();
        $averageOrderValue = $completedOrdersCount > 0 ? $totalSales / $completedOrdersCount : 0;

        $bestSellerItem = OrderItem::selectRaw('product_id, SUM(qty) as total_qty')
            ->whereHas('order', function (Builder $query) use ($store, $start, $end, $isValid) {
                $query->where('store_id', $store->id)->whereIn('status', ['paid', 'shipped', 'delivered']);

                if ($isValid) {
                    $this->applyDateRange($query, $start, $end);
                } else {
                    $query->whereRaw('1 = 0');
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->first();

        $bestSellerName = $bestSellerItem ? $bestSellerItem->product->name : '-';
        $chart = $this->buildRevenueChart($sales, $start, $end);

        return view('livewire.seller.reports', [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'completedOrdersCount' => $completedOrdersCount,
            'averageOrderValue' => $averageOrderValue,
            'bestSellerName' => $bestSellerName,
            'chartLabels' => $chart['labels'],
            'chartRevenue' => $chart['revenue'],
            'chartHasData' => $chart['hasData'],
            'chartTitle' => $chart['title'],
            'periodLabel' => $this->periodLabel($start, $end),
            'pdfQuery' => $this->pdfQuery(),
            'store' => $store,
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

    private function buildRevenueChart(Collection $sales, ?Carbon $start, ?Carbon $end): array
    {
        if ($sales->isEmpty()) {
            return [
                'labels' => [],
                'revenue' => [],
                'hasData' => false,
                'title' => 'Tren Pendapatan',
            ];
        }

        $isDaily = $start && $end && $start->diffInDays($end) <= 31;
        $labels = [];
        $revenue = [];

        if ($isDaily) {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $key = $date->format('Y-m-d');
                $labels[$key] = $date->translatedFormat('d M');
                $revenue[$key] = 0;
            }

            foreach ($sales as $sale) {
                $key = $sale->created_at->format('Y-m-d');
                $revenue[$key] = ($revenue[$key] ?? 0) + (int) $sale->total_price;
                $labels[$key] = $labels[$key] ?? $sale->created_at->translatedFormat('d M');
            }

            ksort($labels);
            ksort($revenue);

            return [
                'labels' => array_values($labels),
                'revenue' => array_values($revenue),
                'hasData' => array_sum($revenue) > 0,
                'title' => 'Tren Pendapatan Harian',
            ];
        }

        foreach ($sales->sortBy('created_at') as $sale) {
            $key = $sale->created_at->format('Y-m');
            $labels[$key] = $sale->created_at->translatedFormat('M Y');
            $revenue[$key] = ($revenue[$key] ?? 0) + (int) $sale->total_price;
        }

        return [
            'labels' => array_values($labels),
            'revenue' => array_values($revenue),
            'hasData' => array_sum($revenue) > 0,
            'title' => 'Tren Pendapatan Bulanan',
        ];
    }
}
