<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| UMKM Air Hitam — Web Routes (Frontend Only)
|--------------------------------------------------------------------------
| Semua route di bawah ini hanya return view tanpa controller/middleware.
| Nanti saat backend dibangun, route ini akan dipindahkan ke controller.
|--------------------------------------------------------------------------
*/

// 🔒 SECURITY FIX: Define rate limiters (ISSUE-016)
RateLimiter::for('public', function (Request $request) {
    return Limit::perMinute(120)->by($request->ip());
});

RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// ============================================
// PUBLIC STOREFRONT (with rate limiting)
// ============================================

Route::middleware(['throttle:public'])->group(function() {
    Route::get('/', function() {
        $categories = \App\Models\Category::query()->limit(6)->get();
        $products = \App\Models\Product::query()->with(['category', 'store'])
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->withSoldQuantity()
            ->whereHas('store', function (\Illuminate\Database\Eloquent\Builder $q) {
                $q->where('status', 'approved');
            })->orderByDesc('sold_quantity')->limit(8)->get();
        $stores = \App\Models\Store::query()->where('status', 'approved')->limit(4)->get();
        
        return view('home', compact('categories', 'products', 'stores'));
    })->name('home');

    Route::view('/panduan', 'panduan')->name('panduan');
    
    Route::get('/products', \App\Livewire\ProductCatalog::class)->name('products.index');
    Route::get('/products/{slug}', \App\Livewire\ProductDetail::class)->name('products.show');
    Route::get('/stores', \App\Livewire\StoreCatalog::class)->name('stores.index');
    Route::get('/stores/{slug}', \App\Livewire\StoreDetail::class)->name('stores.show');

    // Halaman kurir — link sekali pakai (Token_Rahasia), tanpa login/akun kurir
    Route::get('/lacak-kurir/{token}', \App\Livewire\CourierTracking::class)->name('courier.tracking');
});

// ============================================
// AUTH
// ============================================

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login')->middleware('guest');
Route::get('/register', \App\Livewire\Auth\Register::class)->name('register')->middleware('guest');
Route::get('/register-seller', \App\Livewire\Auth\RegisterSeller::class)->name('register.seller')->middleware('guest');
Route::get('/forgot-password', \App\Livewire\Auth\ForgotPassword::class)->name('password.request')->middleware('guest');
Route::get('/reset-password/{token}', \App\Livewire\Auth\ResetPassword::class)->name('password.reset')->middleware('guest');
Route::any('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');

// ============================================
// CUSTOMER (with rate limiting)
// ============================================

Route::middleware(['throttle:auth'])->group(function() {

    Route::middleware(['auth', 'role:customer'])->group(function() {
        Route::get('/cart', \App\Livewire\CartPage::class)->name('cart');
        Route::get('/checkout', \App\Livewire\Checkout::class)->name('checkout');
        Route::get('/customer/dashboard', \App\Livewire\Customer\Dashboard::class)->name('customer.dashboard');
        Route::get('/customer/orders', \App\Livewire\Customer\Orders::class)->name('customer.orders');
        Route::get('/customer/orders/{id}', \App\Livewire\Customer\OrderDetails::class)->name('customer.orders.show');
    });
});

// ============================================
// SELLER DASHBOARD (with rate limiting)
// ============================================

Route::middleware(['auth', 'role:seller', 'throttle:auth'])->group(function() {
    Route::get('/seller/dashboard', \App\Livewire\Seller\Dashboard::class)->name('seller.dashboard');
    
    Route::middleware(['seller.approved'])->group(function() {
        Route::get('/seller/products', \App\Livewire\Seller\Products::class)->name('seller.products');
        Route::get('/seller/orders/{id?}', \App\Livewire\Seller\Orders::class)->name('seller.orders');
        Route::get('/seller/reports', \App\Livewire\Seller\Reports::class)->name('seller.reports');
        Route::get('/seller/reports/pdf', [\App\Http\Controllers\SellerReportController::class, 'downloadPdf'])->name('seller.reports.pdf');
        Route::get('/seller/profile', \App\Livewire\Seller\StoreProfile::class)->name('seller.profile');

        // Bank Account Management — Xendit (Livewire)
        Route::get('/seller/bank', \App\Livewire\Seller\BankAccount::class)->name('seller.bank.index');
    });
});

// ============================================
// ADMIN DASHBOARD (with rate limiting)
// ============================================

Route::middleware(['auth', 'role:admin', 'throttle:auth'])->group(function() {
    Route::get('/admin/dashboard', \App\Livewire\Admin\Dashboard::class)->name('admin.dashboard');
    Route::get('/admin/sellers', \App\Livewire\Admin\Sellers::class)->name('admin.sellers');
    Route::get('/admin/categories', \App\Livewire\Admin\Categories::class)->name('admin.categories');
    Route::get('/admin/reports', \App\Livewire\Admin\Reports::class)->name('admin.reports');
    Route::get('/admin/reports/pdf', [\App\Http\Controllers\AdminReportController::class, 'downloadPdf'])->name('admin.reports.pdf');

    // Bank Verification — Xendit (Livewire)
    Route::get('/admin/bank-verification', \App\Livewire\Admin\BankVerification::class)->name('admin.bank.index');
});
