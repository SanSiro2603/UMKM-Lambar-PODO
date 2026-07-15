<?php

namespace App\Livewire\Seller;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    public function mount()
    {
        $user = Auth::user();
        if ($user && $user->store && $user->store->status === 'rejected') {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('login')
                ->with('error', 'Akun toko Anda telah ditolak oleh admin. Silakan hubungi admin untuk informasi lebih lanjut.');
        }
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'seller' || !$user->store) {
            abort(403);
        }

        $store = $user->store;
        
        // Check if store is pending
        if ($store->status === 'pending') {
            return view('livewire.seller.dashboard-pending', [
                'store' => $store,
            ]);
        }

        // Stats
        $totalProducts = Product::query()->where('store_id', $store->id)->count();
        
        $newOrders = Order::query()->where('store_id', $store->id)
            ->where('status', 'waiting_payment')
            ->count();
            
        $revenue = Order::query()->where('store_id', $store->id)
            ->whereIn('status', ['paid', 'shipped', 'delivered'])
            ->sum('total_price');

        $totalCustomers = Order::query()->where('store_id', $store->id)
            ->distinct('customer_id')
            ->count('customer_id');

        // Recent Orders
        $recentOrders = Order::query()->where('store_id', $store->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Split payment summary (Xendit transactions for this seller)
        $splitTransactions = \App\Models\Transaction::query()->where('seller_id', $user->id)
            ->whereIn('status', ['paid', 'disbursed'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $totalSalesSplit = \App\Models\Transaction::query()->where('seller_id', $user->id)
            ->where('status', 'disbursed')
            ->sum('seller_amount');

        $pendingDisbursement = \App\Models\Transaction::query()->where('seller_id', $user->id)
            ->where('status', 'paid')
            ->sum('seller_amount');

        // 7-day sales calculation
        $days = [];
        $sales = [];
        
        $dayNamesIndonesian = [
            'Mon' => 'Senin',
            'Tue' => 'Selasa',
            'Wed' => 'Rabu',
            'Thu' => 'Kamis',
            'Fri' => 'Jumat',
            'Sat' => 'Sabtu',
            'Sun' => 'Minggu',
        ];

        for ($i = 6; $i >= 0; $i--) {
            $targetDate = now()->subDays($i);
            $dayName = $targetDate->format('D');
            $days[] = $dayNamesIndonesian[$dayName] ?? $dayName;
            
            // Timezone-safe date matching using whereBetween start & end of day
            $daySales = Order::query()->where('store_id', $store->id)
                ->whereBetween('created_at', [$targetDate->copy()->startOfDay(), $targetDate->copy()->endOfDay()])
                ->whereNotIn('status', ['cancelled'])
                ->sum('total_price');
                
            $sales[] = (int) $daySales;
        }

        // Order Status Distribution Donut Chart
        $statusCounts = Order::query()->where('store_id', $store->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [
            'waiting_payment' => 'Menunggu Pembayaran',
            'paid' => 'Sudah Dibayar',
            'shipped' => 'Dikirim',
            'delivered' => 'Selesai',
            'cancelled' => 'Dibatalkan'
        ];

        $donutLabels = [];
        $donutValues = [];
        foreach ($statusLabels as $statusKey => $label) {
            $count = $statusCounts[$statusKey] ?? 0;
            if ($count > 0) {
                $donutLabels[] = $label;
                $donutValues[] = (int) $count;
            }
        }

        $topSellingProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.store_id', $store->id)
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('products.name, SUM(order_items.qty) as sold_qty')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->get();

        $topRevenueProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.store_id', $store->id)
            ->whereIn('orders.status', ['paid', 'shipped', 'delivered'])
            ->selectRaw('products.name, SUM(order_items.qty * order_items.price) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        $paymentCounts = Order::query()->where('store_id', $store->id)
            ->selectRaw('payment_method, COUNT(*) as count')
            ->groupBy('payment_method')
            ->pluck('count', 'payment_method')
            ->toArray();

        $paymentLabelsMap = [
            'xendit' => 'Xendit (Online)',
            'cod' => 'COD',
        ];

        $paymentMethodLabels = [];
        $paymentMethodValues = [];
        foreach ($paymentLabelsMap as $method => $label) {
            $count = $paymentCounts[$method] ?? 0;
            if ($count > 0) {
                $paymentMethodLabels[] = $label;
                $paymentMethodValues[] = (int) $count;
            }
        }

        $lowStockProducts = Product::query()->where('store_id', $store->id)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(5)
            ->get(['name', 'stock']);

        return view('livewire.seller.dashboard', [
            'store' => $store,
            'totalProducts' => $totalProducts,
            'newOrders' => $newOrders,
            'revenue' => $revenue,
            'totalCustomers' => $totalCustomers,
            'recentOrders' => $recentOrders,
            'splitTransactions' => $splitTransactions,
            'totalSalesSplit' => $totalSalesSplit,
            'pendingDisbursement' => $pendingDisbursement,
            'chartDays' => $days,
            'chartSales' => $sales,
            'donutLabels' => $donutLabels,
            'donutValues' => $donutValues,
            'topProductLabels' => $topSellingProducts->pluck('name')->values()->all(),
            'topProductValues' => $topSellingProducts->pluck('sold_qty')->map(fn ($value) => (int) $value)->values()->all(),
            'topRevenueProductLabels' => $topRevenueProducts->pluck('name')->values()->all(),
            'topRevenueProductValues' => $topRevenueProducts->pluck('revenue')->map(fn ($value) => (int) $value)->values()->all(),
            'paymentMethodLabels' => $paymentMethodLabels,
            'paymentMethodValues' => $paymentMethodValues,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
