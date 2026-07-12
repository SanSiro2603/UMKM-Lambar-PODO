<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Livewire\Attributes\Computed;

class Checkout extends Component
{
    public array $paymentMethods = []; // store_id => method ('xendit' or 'cod')
    public array $purchasedProductIds = [];
    public string $shippingAddress = '';
    public string $shippingPhone = '';
    public ?int $buyNowProductId = null;
    public int $buyNowQty = 1;

    public function mount()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk checkout.');
        }

        $user = Auth::user();
        if (empty($user->address)) {
            session()->flash('error', 'Silakan atur alamat pengiriman Anda di profil terlebih dahulu sebelum melakukan checkout.');
            return redirect()->route('customer.dashboard');
        }

        $this->shippingAddress = $user->address;
        $this->shippingPhone = $user->phone ?? '';
        $this->buyNowProductId = request()->query('product_id') ? (int) request()->query('product_id') : null;
        $this->buyNowQty = request()->query('qty') ? (int) request()->query('qty') : 1;

        if ($this->checkDuplicateOrder()) {
            session()->flash('error', 'Anda sudah membuat pesanan yang sama sebelumnya. Silakan periksa detail pesanan Anda.');
            return redirect()->route('customer.orders');
        }
    }

    private function checkDuplicateOrder(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $grouped = $this->groupedItems;
        if (empty($grouped)) {
            return false;
        }

        foreach ($grouped as $storeId => $data) {
            // 🔒 SECURITY FIX: Check all active orders, not just 5-minute window (ISSUE-014)
            $recentOrders = Order::where('customer_id', $user->id)
                ->where('store_id', $storeId)
                ->whereIn('status', ['waiting_shipping_cost', 'waiting_payment', 'paid', 'shipped'])
                ->with('items')
                ->get();

            foreach ($recentOrders as $existingOrder) {
                $existingItems = $existingOrder->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'qty' => $item->qty,
                    ];
                })->sortBy('product_id')->values()->toArray();

                $currentItems = collect($data['items'])->map(function ($item) {
                    return [
                        'product_id' => $item['product']->id,
                        'qty' => $item['qty'],
                    ];
                })->sortBy('product_id')->values()->toArray();

                if ($existingItems === $currentItems) {
                    return true;
                }
            }
        }

        return false;
    }

    #[Computed]
    public function groupedItems()
    {
        $groups = [];

        if ($this->buyNowProductId) {
            $product = Product::with('store.paymentMethods')->find($this->buyNowProductId);
            if ($product && $product->store->status === 'approved') {
                $storeId = $product->store_id;
                
                if (!isset($this->paymentMethods[$storeId])) {
                    $this->paymentMethods[$storeId] = 'xendit';
                }

                $qty = $this->buyNowQty;
                if ($qty > 0) {
                    $subtotal = $product->price * $qty;
                    $groups[$storeId] = [
                        'store' => $product->store,
                        'items' => [
                            [
                                'product' => $product,
                                'qty' => $qty,
                                'price' => $product->price,
                                'subtotal' => $subtotal
                            ]
                        ],
                        'subtotal' => $subtotal
                    ];
                }
            }
            return $groups;
        }

        // Fetch selected items from DB
        $cartItems = \App\Models\CartItem::with(['product.store.paymentMethods'])
            ->where('user_id', Auth::id())
            ->where('selected', true)
            ->get();

        foreach ($cartItems as $item) {
            $product = $item->product;
            if ($product && $product->store->status === 'approved') {
                $storeId = $product->store_id;
                
                if (!isset($groups[$storeId])) {
                    $groups[$storeId] = [
                        'store' => $product->store,
                        'items' => [],
                        'subtotal' => 0
                    ];
                    
                    if (!isset($this->paymentMethods[$storeId])) {
                        $this->paymentMethods[$storeId] = 'xendit';
                    }
                }
                
                $qty = $item->qty; // Keep requested qty to let validation catch over-stock
                if ($qty <= 0) continue;

                $subtotal = $product->price * $qty;
                
                $groups[$storeId]['items'][] = [
                    'product' => $product,
                    'qty' => $qty,
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ];
                
                $groups[$storeId]['subtotal'] += $subtotal;
            }
        }

        return $groups;
    }

    public function placeOrder()
    {
        $this->validate([
            'shippingAddress' => 'required|string|min:10',
            'shippingPhone'   => 'required|string|min:9|max:15|regex:/^[0-9+]+$/',
        ], [
            'shippingPhone.required' => 'Nomor HP wajib diisi agar kurir dapat menghubungi Anda.',
            'shippingPhone.regex'    => 'Nomor HP hanya boleh berisi angka.',
        ]);

        // Validasi alamat hanya pengiriman wilayah Lampung Barat
        if (!str_contains(strtolower($this->shippingAddress), 'lampung barat')) {
            session()->flash('error', 'Maaf, saat ini kami hanya melayani pengiriman ke wilayah Kabupaten Lampung Barat. Silakan perbarui alamat Anda di dashboard.');
            return;
        }

        if ($this->checkDuplicateOrder()) {
            session()->flash('error', 'Pesanan serupa telah dibuat sebelumnya. Silakan periksa daftar pesanan Anda.');
            return redirect()->route('customer.orders');
        }

        $grouped = $this->groupedItems;
        if (empty($grouped)) {
            session()->flash('error', 'Tidak ada produk yang dipilih untuk checkout.');
            return;
        }

        // 🔒 SECURITY FIX: Wrap in database transaction (ISSUE-002)
        try {
            DB::transaction(function() use ($grouped) {
                // Stock validation before creating orders
                $stockErrors = [];
                foreach ($grouped as $storeId => $data) {
                    foreach ($data['items'] as $item) {
                        $product = $item['product'];
                        $qty = $item['qty'];

                        // 🔒 SECURITY FIX: Pessimistic locking (ISSUE-002)
                        $freshProduct = Product::where('id', $product->id)
                                               ->lockForUpdate()
                                               ->first();

                        if (!$freshProduct || $freshProduct->stock <= 0) {
                            $stockErrors[] = "Stok \"{$product->name}\" sudah habis. Silakan hapus dari keranjang.";
                        } elseif ($qty > $freshProduct->stock) {
                            $stockErrors[] = "Stok \"{$product->name}\" tidak mencukupi. Tersedia {$freshProduct->stock} pcs, Anda memesan {$qty} pcs. Silakan kurangi jumlah.";
                        }
                    }
                }

                if (!empty($stockErrors)) {
                    throw new \Exception(implode('<br>', $stockErrors));
                }

                foreach ($grouped as $storeId => $data) {
                    $totalPrice = $data['subtotal'];
                    $paymentMethod = $this->paymentMethods[$storeId] ?? 'xendit';
                    $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(4));

                    $order = Order::create([
                        'order_code' => $orderCode,
                        'customer_id' => Auth::id(),
                        'store_id' => $storeId,
                        'total_price' => $totalPrice,
                        'shipping_cost' => null,
                        'shipping_address' => $this->shippingAddress,
                        'shipping_phone' => $this->shippingPhone,
                        'payment_method' => $paymentMethod,
                        'payment_status' => 'unpaid',
                        'status' => 'waiting_shipping_cost',
                    ]);

                    foreach ($data['items'] as $item) {
                        $product = $item['product'];

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'qty' => $item['qty'],
                            'price' => $item['price']
                        ]);

                        // 🔒 SECURITY FIX: Atomic decrement with condition (ISSUE-002)
                        $updated = DB::table('products')
                            ->where('id', $product->id)
                            ->where('stock', '>=', $item['qty'])
                            ->decrement('stock', $item['qty']);

                        if (!$updated) {
                            throw new \Exception("Stock depleted for product ID {$product->id}");
                        }

                        $this->purchasedProductIds[] = $product->id;
                    }

                    // Broadcast event to seller
                    event(new \App\Events\OrderPaymentUploaded($order, 'Pesanan baru telah masuk!'));
                }

                if (!$this->buyNowProductId) {
                    \App\Models\CartItem::where('user_id', Auth::id())
                        ->whereIn('product_id', $this->purchasedProductIds)
                        ->delete();
                    $this->dispatch('cart-updated')->to(CartManager::class);
                    $this->dispatch('cart-badge-bounce');
                }
            });

            session()->flash('success', 'Pesanan berhasil dibuat! Menunggu penjual menentukan ongkos kirim.');
            return redirect()->route('customer.orders');

        } catch (\Exception $e) {
            // 🔒 SECURITY FIX: Generic error message (ISSUE-005)
            Log::error('Checkout failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.checkout')->extends('layouts.app')->section('content');
    }
}
