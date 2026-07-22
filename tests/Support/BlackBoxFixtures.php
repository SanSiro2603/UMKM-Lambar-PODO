<?php

namespace Tests\Support;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;

trait BlackBoxFixtures
{
    protected function createBlackBoxRegions(): void
    {
        $prefix = config('laravolt.indonesia.table_prefix');
        if (Schema::hasTable($prefix.'districts')) {
            return;
        }

        Schema::create($prefix.'provinces', function (Blueprint $table): void {
            $table->string('code')->primary();
            $table->string('name');
            $table->json('meta')->nullable();
        });
        Schema::create($prefix.'cities', function (Blueprint $table): void {
            $table->string('code')->primary();
            $table->string('province_code');
            $table->string('name');
            $table->json('meta')->nullable();
        });
        Schema::create($prefix.'districts', function (Blueprint $table): void {
            $table->string('code')->primary();
            $table->string('city_code');
            $table->string('name');
            $table->json('meta')->nullable();
        });
        Schema::create($prefix.'villages', function (Blueprint $table): void {
            $table->string('code')->primary();
            $table->string('district_code');
            $table->string('name');
            $table->json('meta')->nullable();
        });

        Province::create(['code' => '18', 'name' => 'LAMPUNG']);
        City::create(['code' => '1804', 'province_code' => '18', 'name' => 'LAMPUNG BARAT']);
        District::create(['code' => '180404', 'city_code' => '1804', 'name' => 'BALIK BUKIT']);
        District::create(['code' => '180419', 'city_code' => '1804', 'name' => 'AIR HITAM']);
        Village::create(['code' => '1804042001', 'district_code' => '180404', 'name' => 'PASAR LIWA']);
        Village::create(['code' => '1804192001', 'district_code' => '180419', 'name' => 'SUMBER ALAM']);
    }

    protected function makeBlackBoxUser(string $role = 'customer', array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'phone' => '081234567890',
            'address' => 'Jalan Uji No. 1, Desa/Kel. SUMBER ALAM, Kec. AIR HITAM, Kabupaten Lampung Barat',
            'district_code' => '180419',
        ], $attributes));
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    protected function makeBlackBoxStore(string $status = 'approved', array $attributes = []): Store
    {
        $seller = $attributes['seller'] ?? $this->makeBlackBoxUser('seller');
        unset($attributes['seller']);
        $suffix = Str::lower(Str::random(8));

        return Store::create(array_merge([
            'user_id' => $seller->id,
            'name' => 'Toko Uji '.$suffix,
            'slug' => 'toko-uji-'.$suffix,
            'description' => 'Toko pengujian pelanggan',
            'address' => 'Jalan Toko, Kabupaten Lampung Barat',
            'district_code' => '180419',
            'status' => $status,
        ], $attributes));
    }

    protected function makeBlackBoxProduct(?Store $store = null, array $attributes = []): Product
    {
        $store ??= $this->makeBlackBoxStore();
        $suffix = Str::lower(Str::random(8));
        $category = Category::create([
            'name' => 'Kategori '.$suffix,
            'slug' => 'kategori-'.$suffix,
            'icon' => 'store',
        ]);

        return Product::create(array_merge([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Produk Uji '.$suffix,
            'slug' => 'produk-uji-'.$suffix,
            'description' => 'Produk untuk pengujian pelanggan.',
            'price' => 50000,
            'stock' => 10,
        ], $attributes));
    }

    protected function makeBlackBoxOrder(
        User $customer,
        Product $product,
        string $status = 'waiting_payment',
        array $attributes = [],
        int $quantity = 1,
    ): Order {
        $shipping = $attributes['shipping_cost'] ?? 5000;
        $order = Order::create(array_merge([
            'order_code' => 'ORD-BB-'.Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'store_id' => $product->store_id,
            'total_price' => ($product->price * $quantity) + $shipping,
            'shipping_cost' => $shipping,
            'shipping_zone_label' => 'Kecamatan sama',
            'shipping_address' => $customer->address,
            'shipping_phone' => $customer->phone,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => $status,
        ], $attributes));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => $quantity,
            'price' => $product->price,
        ]);

        return $order->load('items.product', 'store');
    }
}
