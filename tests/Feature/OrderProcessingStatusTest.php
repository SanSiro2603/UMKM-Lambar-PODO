<?php

namespace Tests\Feature;

use App\Events\OrderStatusUpdated;
use App\Livewire\CourierTracking;
use App\Livewire\Customer\OrderDetails;
use App\Livewire\Customer\Orders as CustomerOrders;
use App\Livewire\Seller\Orders as SellerOrders;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\Transaction;
use App\Models\User;
use App\Services\XenditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class OrderProcessingStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_cod_processing_status_is_described_consistently_to_customer_and_seller(): void
    {
        ['customer' => $customer, 'seller' => $seller, 'order' => $order] = $this->createOrder();

        Livewire::actingAs($customer)
            ->test(CustomerOrders::class)
            ->assertSee('Siap Diproses')
            ->assertDontSee('Sudah Dibayar');

        Livewire::actingAs($customer)
            ->test(OrderDetails::class, ['id' => $order->id])
            ->assertSee('Pesanan COD siap diproses.')
            ->assertSee('Bayar saat barang diterima.')
            ->assertDontSee('Pembayaran berhasil dikonfirmasi!');

        Livewire::actingAs($seller)
            ->test(SellerOrders::class, ['id' => $order->id])
            ->assertSee('Siap Diproses')
            ->assertSee('BAYAR SAAT BARANG DITERIMA')
            ->assertSee('menagih pembayaran saat barang diterima')
            ->assertDontSee('Sudah Dibayar');
    }

    public function test_seller_can_send_processing_order_to_courier(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        ['seller' => $seller, 'order' => $order] = $this->createOrder();

        Livewire::actingAs($seller)
            ->test(SellerOrders::class, ['id' => $order->id])
            ->set('courierName', 'Kurir Podo')
            ->set('courierPhone', '081234567899')
            ->call('sendCourierAccess')
            ->assertHasNoErrors()
            ->assertDispatched('open-whatsapp');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
            'payment_status' => 'unpaid',
            'courier_name' => 'Kurir Podo',
        ]);
    }

    public function test_courier_completion_marks_cod_as_paid_and_delivered(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        ['order' => $order] = $this->createOrder([
            'status' => 'shipped',
            'courier_token' => 'cod-courier-token',
        ]);

        Livewire::test(CourierTracking::class, ['token' => 'cod-courier-token'])
            ->call('completeDelivery');

        $order->refresh();

        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertNull($order->courier_token);
    }

    public function test_xendit_paid_webhook_moves_order_to_processing_and_keeps_transaction_paid(): void
    {
        ['seller' => $seller, 'order' => $order] = $this->createOrder([
            'payment_method' => 'xendit',
            'status' => 'waiting_payment',
            'xendit_invoice_id' => 'invoice-processing-test',
            'xendit_invoice_url' => 'https://example.test/invoice-processing-test',
        ]);

        $transaction = Transaction::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'total_amount' => $order->total_price,
            'xendit_invoice_id' => 'invoice-processing-test',
            'status' => 'pending',
        ]);

        $xendit = Mockery::mock(XenditService::class);
        $xendit->shouldReceive('verifyWebhookToken')->once()->with('valid-token')->andReturnTrue();
        $xendit->shouldReceive('disbursementToSeller')->once()->andReturn(['success' => true]);
        $this->app->instance(XenditService::class, $xendit);

        $this->withHeader('x-callback-token', 'valid-token')
            ->postJson(route('api.webhook.xendit'), [
                'id' => 'invoice-processing-test',
                'status' => 'PAID',
                'payment_method' => 'BANK_TRANSFER',
                'payment_channel' => 'BRI',
            ])
            ->assertOk();

        $order->refresh();
        $transaction->refresh();

        $this->assertSame('processing', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame('paid', $transaction->status);
    }

    public function test_stale_xendit_webhooks_do_not_change_order_after_invoice_replacement(): void
    {
        ['seller' => $seller, 'order' => $order] = $this->createOrder([
            'payment_method' => 'xendit',
            'status' => 'waiting_payment',
            'xendit_invoice_id' => null,
            'xendit_invoice_url' => null,
        ]);

        Transaction::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'total_amount' => $order->total_price + 15000,
            'xendit_invoice_id' => 'stale-address-invoice',
            'status' => 'expired',
            'expired_at' => now(),
            'metadata' => ['expired_reason' => 'shipping_address_changed'],
        ]);

        $xendit = Mockery::mock(XenditService::class);
        $xendit->shouldReceive('verifyWebhookToken')->twice()->with('valid-token')->andReturnTrue();
        $xendit->shouldNotReceive('disbursementToSeller');
        $this->app->instance(XenditService::class, $xendit);

        foreach (['PAID', 'EXPIRED'] as $status) {
            $this->withHeader('x-callback-token', 'valid-token')
                ->postJson(route('api.webhook.xendit'), [
                    'id' => 'stale-address-invoice',
                    'status' => $status,
                ])
                ->assertOk()
                ->assertJson(['message' => 'Stale invoice ignored']);
        }

        $order->refresh();
        $this->assertSame('waiting_payment', $order->status);
        $this->assertSame('unpaid', $order->payment_status);
        $this->assertNull($order->paid_at);
    }

    private function createOrder(array $overrides = []): array
    {
        $seller = User::factory()->create();
        $seller->forceFill(['role' => 'seller'])->save();

        $customer = User::factory()->create([
            'address' => 'Jalan Mawar, Kecamatan Balik Bukit, Kabupaten Lampung Barat',
            'phone' => '081234567890',
        ]);
        $customer->forceFill(['role' => 'customer'])->save();

        $store = Store::create([
            'user_id' => $seller->id,
            'name' => 'Toko Podo',
            'slug' => 'toko-podo',
            'address' => 'Pasar Liwa, Kabupaten Lampung Barat',
            'status' => 'approved',
        ]);

        $category = Category::create([
            'name' => 'Kopi',
            'slug' => 'kopi',
            'icon' => 'store',
        ]);

        $product = Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Kopi Podo',
            'slug' => 'kopi-podo',
            'description' => 'Kopi lokal.',
            'price' => 75000,
            'stock' => 5,
        ]);

        $order = Order::create(array_merge([
            'order_code' => 'ORD-STATUS-TEST',
            'customer_id' => $customer->id,
            'store_id' => $store->id,
            'total_price' => 80000,
            'shipping_cost' => 5000,
            'shipping_zone_label' => 'Kecamatan sama',
            'shipping_address' => $customer->address,
            'shipping_phone' => $customer->phone,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'processing',
        ], $overrides));

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 75000,
        ]);

        return compact('seller', 'customer', 'store', 'product', 'order');
    }
}
