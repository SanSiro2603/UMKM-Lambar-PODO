<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Store;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.dashboard')]
class Dashboard extends Component
{
    public function approveSeller(int $id)
    {
        $store = Store::findOrFail($id);
        $store->update(['status' => 'approved']);
        
        // Also update the user role to 'seller' in case it wasn't set (though onboarding does this)
        $store->user->update(['role' => 'seller']);

        session()->flash('success', 'Toko ' . $store->name . ' berhasil disetujui.');
    }

    public function rejectSeller(int $id)
    {
        $store = Store::findOrFail($id);
        $store->update(['status' => 'rejected']);
        
        session()->flash('success', 'Toko ' . $store->name . ' ditolak.');
    }

    public function render()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403);
        }

        $totalSellers = Store::query()->where('status', 'approved')->count();
        $totalProducts = Product::count();
        $totalOrders = Order::query()->whereNotIn('status', ['cancelled'])->count();

        // Revenue: dari Transaction (platform fee) + Order yg COD/selesai
        $platformRevenue = \App\Models\Transaction::query()->where('status', 'disbursed')->sum('platform_fee');
        $totalOmzet = Order::query()->whereIn('status', ['processing', 'shipped', 'delivered'])->sum('total_price');
        $revenue = $platformRevenue; // platform actual income

        // Split payment summary
        $totalDisbursed = \App\Models\Transaction::query()->where('status', 'disbursed')->sum('seller_amount');
        $pendingDisbursement = \App\Models\Transaction::query()->where('status', 'paid')->sum('seller_amount');

        $pendingSellers = Store::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 1. Leaderboard Toko Terlaris (Top Stores by Revenue)
        $topStores = Order::query()->whereIn('status', ['processing', 'shipped', 'delivered'])
            ->selectRaw('store_id, SUM(total_price) as total_revenue, COUNT(*) as total_sales')
            ->groupBy('store_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->with('store')
            ->get();

        // 2. Kategori Terpopuler (Best-selling Categories)
        $topCategories = \App\Models\OrderItem::selectRaw('categories.name as category_name, SUM(order_items.qty) as total_qty')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.status', ['processing', 'shipped', 'delivered'])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $categoryLabels = [];
        $categoryValues = [];
        foreach ($topCategories as $cat) {
            $categoryLabels[] = $cat->category_name;
            $categoryValues[] = (int) $cat->total_qty;
        }

        // 3. Recent transactions (split payment)
        $recentTransactions = \App\Models\Transaction::with(['order.store', 'seller'])
            ->whereIn('status', ['paid', 'disbursed'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 4. Platform growth chart (monthly orders count of current year)
        $monthlyOrders = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        for ($m = 1; $m <= 12; $m++) {
            $count = Order::query()->whereMonth('created_at', $m)
                ->whereYear('created_at', now()->year)
                ->whereNotIn('status', ['cancelled'])
                ->count();

            $monthlyOrders[] = (int) $count;
        }

        return view('livewire.admin.dashboard', [
            'totalSellers' => $totalSellers,
            'totalProducts' => $totalProducts,
            'totalOrders' => $totalOrders,
            'revenue' => $revenue,
            'totalOmzet' => $totalOmzet,
            'totalDisbursed' => $totalDisbursed,
            'platformRevenue' => $platformRevenue,
            'pendingDisbursement' => $pendingDisbursement,
            'pendingSellers' => $pendingSellers,
            'topStores' => $topStores,
            'recentTransactions' => $recentTransactions,
            'categoryLabels' => $categoryLabels,
            'categoryValues' => $categoryValues,
            'chartMonths' => $months,
            'chartOrders' => $monthlyOrders,
        ]);
    }
}
