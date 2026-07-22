<?php

namespace Tests\Feature;

use App\Events\OrderShippingUpdated;
use App\Livewire\Customer\Dashboard as CustomerDashboard;
use App\Livewire\Customer\OrderDetails;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActiveOrderShippingSyncService;
use App\Services\ShippingZoneCalculator;
use App\Services\XenditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravolt\Indonesia\Models\City;
use Laravolt\Indonesia\Models\District;
use Laravolt\Indonesia\Models\Province;
use Laravolt\Indonesia\Models\Village;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\TestDox;

class ActiveOrderShippingSyncTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('PLG-DSH-006 Perubahan alamat memperbarui ongkir pesanan aktif')]
    public function test_customer_dashboard_previews_and_persists_active_order_shipping_changes(): void
    {
        Event::fake([OrderShippingUpdated::class]);
        $this->createRegions();
        $scenario = $this->createOrder([
            'store_district' => '180419',
            'shipping_cost' => 20000,
            'total_price' => 95000,
        ]);
        $scenario['customer']->update([
            'address' => 'Jalan Lama, Desa/Kel. Pasar Liwa, Kec. Balik Bukit, Kabupaten Lampung Barat',
            'district_code' => '180404',
        ]);
        $this->app->instance(ActiveOrderShippingSyncService::class, $this->serviceWithoutInvoiceCalls());

        Livewire::actingAs($scenario['customer'])
            ->test(CustomerDashboard::class)
            ->call('editAddress')
            ->set('selectedDistrictCode', '180419')
            ->set('selectedVillageCode', '1804192001')
            ->set('detailAddress', 'Jalan Baru Nomor 10')
            ->assertSee('Dampak ke pesanan aktif')
            ->assertSee('Rp 5.000')
            ->call('updateAddress')
            ->assertHasNoErrors()
            ->assertSee('Alamat pengiriman berhasil diperbarui.');

        $scenario['customer']->refresh();
        $scenario['order']->refresh();
        $this->assertSame('180419', $scenario['customer']->district_code);
        $this->assertStringContainsString('Jalan Baru Nomor 10', $scenario['customer']->address);
        $this->assertSame($scenario['customer']->address, $scenario['order']->shipping_address);
        $this->assertSame(5000, $scenario['order']->shipping_cost);
        $this->assertSame(80000, $scenario['order']->total_price);
    }

    #[TestDox('PLG-DSH-007 Sinkronisasi COD menghitung ulang dari subtotal item')]
    public function test_cod_order_preview_and_sync_recalculate_shipping_from_item_subtotal(): void
    {
        Event::fake([OrderShippingUpdated::class]);
        $scenario = $this->createOrder([
            'store_district' => '180419',
            'shipping_cost' => 20000,
            'total_price' => 95000,
        ]);
        $service = $this->serviceWithoutInvoiceCalls();

        $preview = $service->preview($scenario['customer'], '180419');

        $this->assertCount(1, $preview['orders']);
        $this->assertSame(20000, $preview['orders'][0]['old_cost']);
        $this->assertSame(5000, $preview['orders'][0]['new_cost']);
        $this->assertSame(80000, $preview['orders'][0]['new_total']);

        $result = $service->sync(
            $scenario['customer'],
            'Alamat Baru, Desa/Kel. Sumber Alam, Kec. Air Hitam, Kabupaten Lampung Barat',
            '180419',
        );

        $scenario['order']->refresh();
        $this->assertSame(1, $result['updated']);
        $this->assertSame(5000, $scenario['order']->shipping_cost);
        $this->assertSame('Kecamatan sama', $scenario['order']->shipping_zone_label);
        $this->assertSame(80000, $scenario['order']->total_price);
        $this->assertStringContainsString('Alamat Baru', $scenario['order']->shipping_address);
        Event::assertDispatched(OrderShippingUpdated::class, fn ($event) => $event->order->is($scenario['order']));
    }

    #[TestDox('PLG-DSH-008 Setiap pesanan menggunakan asal toko masing-masing')]
    public function test_multiple_orders_use_each_store_as_shipping_origin(): void
    {
        Event::fake([OrderShippingUpdated::class]);
        $first = $this->createOrder(['store_district' => '180419']);
        $second = $this->createOrder([
            'customer' => $first['customer'],
            'store_district' => '180404',
        ]);

        $result = $this->serviceWithoutInvoiceCalls()->sync(
            $first['customer'],
            'Alamat Baru, Desa/Kel. Sumber Alam, Kec. Air Hitam, Kabupaten Lampung Barat',
            '180419',
        );

        $this->assertSame(2, $result['updated']);
        $this->assertSame(5000, $first['order']->fresh()->shipping_cost);
        $this->assertNotSame(5000, $second['order']->fresh()->shipping_cost);
    }

    #[TestDox('PLG-DSH-009 Invoice pending dipertahankan jika total ongkir tidak berubah')]
    public function test_pending_invoice_is_kept_when_shipping_amount_does_not_change(): void
    {
        Event::fake([OrderShippingUpdated::class]);
        $scenario = $this->createOrder([
            'payment_method' => 'xendit',
            'status' => 'waiting_payment',
            'store_district' => '180419',
            'shipping_cost' => 5000,
            'total_price' => 80000,
            'invoice_id' => 'invoice-same-cost',
        ]);

        $service = $this->serviceWithoutInvoiceCalls();
        $result = $service->sync(
            $scenario['customer'],
            'Jalan Baru, Desa/Kel. Sumber Alam, Kec. Air Hitam, Kabupaten Lampung Barat',
            '180419',
        );

        $scenario['order']->refresh();
        $this->assertSame(1, $result['updated']);
        $this->assertSame(0, $result['invoice_resets']);
        $this->assertSame('invoice-same-cost', $scenario['order']->xendit_invoice_id);
        $this->assertSame('pending', $scenario['transaction']->fresh()->status);
        $this->assertStringContainsString('Jalan Baru', $scenario['order']->shipping_address);
    }

    #[TestDox('PLG-DSH-010 Invoice pending diganti jika perubahan ongkir mengubah total')]
    public function test_pending_invoice_is_expired_when_new_shipping_changes_total(): void
    {
        Event::fake([OrderShippingUpdated::class]);
        $scenario = $this->createOrder([
            'payment_method' => 'xendit',
            'status' => 'waiting_payment',
            'store_district' => '180419',
            'shipping_cost' => 20000,
            'total_price' => 95000,
            'invoice_id' => 'invoice-old-total',
        ]);

        $xendit = Mockery::mock(XenditService::class);
        $xendit->shouldReceive('expireInvoice')
            ->once()
            ->with('invoice-old-total')
            ->andReturn(['success' => true]);
        $xendit->shouldReceive('createInvoice')->once()->andReturn([
            'success' => true,
            'invoice_id' => 'invoice-new-total',
            'payment_url' => 'https://example.test/invoice-new-total',
            'payment_method' => null,
            'payment_channel' => null,
        ]);
        $this->app->instance(XenditService::class, $xendit);
        $service = new ActiveOrderShippingSyncService(app(ShippingZoneCalculator::class), $xendit);

        $result = $service->sync(
            $scenario['customer'],
            'Alamat Dekat, Desa/Kel. Sumber Alam, Kec. Air Hitam, Kabupaten Lampung Barat',
            '180419',
        );

        $scenario['order']->refresh();
        $scenario['transaction']->refresh();
        $this->assertSame(1, $result['invoice_resets']);
        $this->assertSame(80000, $scenario['order']->total_price);
        $this->assertNull($scenario['order']->xendit_invoice_id);
        $this->assertNull($scenario['order']->xendit_invoice_url);
        $this->assertSame('expired', $scenario['transaction']->status);
        $this->assertSame('shipping_address_changed', $scenario['transaction']->metadata['expired_reason']);

        Livewire::actingAs($scenario['customer'])
            ->test(OrderDetails::class, ['id' => $scenario['order']->id])
            ->call('payWithXendit')
            ->assertRedirect('https://example.test/invoice-new-total');

        $this->assertDatabaseHas('transactions', [
            'order_id' => $scenario['order']->id,
            'xendit_invoice_id' => 'invoice-new-total',
            'total_amount' => 80000,
            'status' => 'pending',
        ]);
    }

    #[TestDox('PLG-DSH-011 Kegagalan penggantian invoice mempertahankan snapshot lama')]
    public function test_failed_invoice_expiry_keeps_order_snapshot_unchanged(): void
    {
        Event::fake([OrderShippingUpdated::class]);
        $scenario = $this->createOrder([
            'payment_method' => 'xendit',
            'status' => 'waiting_payment',
            'store_district' => '180419',
            'shipping_cost' => 20000,
            'total_price' => 95000,
            'invoice_id' => 'invoice-cannot-expire',
        ]);

        $xendit = Mockery::mock(XenditService::class);
        $xendit->shouldReceive('expireInvoice')->once()->andReturn([
            'success' => false,
            'message' => 'API unavailable',
        ]);
        $service = new ActiveOrderShippingSyncService(app(ShippingZoneCalculator::class), $xendit);

        $result = $service->sync(
            $scenario['customer'],
            'Alamat Gagal, Desa/Kel. Sumber Alam, Kec. Air Hitam, Kabupaten Lampung Barat',
            '180419',
        );

        $scenario['order']->refresh();
        $this->assertSame(1, $result['failed']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(95000, $scenario['order']->total_price);
        $this->assertSame('invoice-cannot-expire', $scenario['order']->xendit_invoice_id);
        $this->assertStringNotContainsString('Alamat Gagal', $scenario['order']->shipping_address);
        Event::assertNotDispatched(OrderShippingUpdated::class);
    }

    #[TestDox('PLG-DSH-012 Pesanan lunas dan dikirim mempertahankan alamat serta ongkir lama')]
    public function test_paid_and_shipped_orders_keep_their_original_snapshot(): void
    {
        Event::fake([OrderShippingUpdated::class]);
        $paid = $this->createOrder([
            'payment_method' => 'xendit',
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);
        $shipped = $this->createOrder([
            'customer' => $paid['customer'],
            'status' => 'shipped',
        ]);

        $result = $this->serviceWithoutInvoiceCalls()->sync(
            $paid['customer'],
            'Alamat Yang Tidak Boleh Masuk, Kabupaten Lampung Barat',
            '180419',
        );

        $this->assertSame(0, $result['updated']);
        $this->assertSame(2, $result['skipped']);
        $this->assertStringNotContainsString('Tidak Boleh Masuk', $paid['order']->fresh()->shipping_address);
        $this->assertStringNotContainsString('Tidak Boleh Masuk', $shipped['order']->fresh()->shipping_address);
    }

    private function serviceWithoutInvoiceCalls(): ActiveOrderShippingSyncService
    {
        $xendit = Mockery::mock(XenditService::class);
        $xendit->shouldNotReceive('expireInvoice');

        return new ActiveOrderShippingSyncService(app(ShippingZoneCalculator::class), $xendit);
    }

    private function createRegions(): void
    {
        $prefix = config('laravolt.indonesia.table_prefix');
        Schema::create($prefix . 'provinces', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->json('meta')->nullable();
        });
        Schema::create($prefix . 'cities', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('province_code');
            $table->string('name');
            $table->json('meta')->nullable();
        });
        Schema::create($prefix . 'districts', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('city_code');
            $table->string('name');
            $table->json('meta')->nullable();
        });
        Schema::create($prefix . 'villages', function (Blueprint $table) {
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

    private function createOrder(array $options = []): array
    {
        $customer = $options['customer'] ?? User::factory()->create([
            'address' => 'Alamat Lama, Kabupaten Lampung Barat',
            'phone' => '081234567890',
            'district_code' => '180404',
        ]);
        $customer->forceFill(['role' => 'customer'])->save();

        $seller = User::factory()->create();
        $seller->forceFill(['role' => 'seller'])->save();

        $suffix = strtolower(Str::random(8));
        $store = Store::create([
            'user_id' => $seller->id,
            'name' => 'Toko ' . $suffix,
            'slug' => 'toko-' . $suffix,
            'address' => 'Alamat Toko, Kabupaten Lampung Barat',
            'district_code' => $options['store_district'] ?? '180419',
            'status' => 'approved',
        ]);

        $category = Category::firstOrCreate(
            ['slug' => 'kategori-sync'],
            ['name' => 'Kategori Sync', 'icon' => 'store'],
        );

        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Produk ' . $suffix,
            'slug' => 'produk-' . $suffix,
            'description' => 'Produk pengujian.',
            'price' => 75000,
            'stock' => 5,
        ]);

        $invoiceId = $options['invoice_id'] ?? null;
        $order = Order::create([
            'order_code' => 'ORD-SYNC-' . strtoupper($suffix),
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'total_price' => $options['total_price'] ?? 95000,
            'shipping_cost' => $options['shipping_cost'] ?? 20000,
            'shipping_zone_label' => 'Kecamatan paling jauh',
            'shipping_address' => 'Alamat Lama, Kabupaten Lampung Barat',
            'shipping_phone' => $customer->phone,
            'payment_method' => $options['payment_method'] ?? 'cod',
            'payment_status' => $options['payment_status'] ?? 'unpaid',
            'status' => $options['status'] ?? 'processing',
            'xendit_invoice_id' => $invoiceId,
            'xendit_invoice_url' => $invoiceId ? 'https://example.test/' . $invoiceId : null,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 75000,
        ]);

        $transaction = null;
        if ($invoiceId) {
            $transaction = Transaction::create([
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'total_amount' => $order->total_price,
                'xendit_invoice_id' => $invoiceId,
                'xendit_invoice_url' => $order->xendit_invoice_url,
                'status' => 'pending',
            ]);
        }

        return compact('customer', 'seller', 'store', 'product', 'order', 'transaction');
    }
}
