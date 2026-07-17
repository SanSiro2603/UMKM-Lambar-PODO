<?php

namespace Tests\Feature;

use App\Events\OrderPaymentUploaded;
use App\Livewire\Checkout;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutShippingTest extends TestCase
{
    use RefreshDatabase;

    public function test_buy_now_checkout_creates_order_with_automatic_shipping_zone(): void
    {
        Event::fake([OrderPaymentUploaded::class]);

        $product = $this->createApprovedProduct();
        $customer = $this->createCustomer();

        $component = Livewire::withQueryParams([
            'product_id' => $product->id,
            'qty' => 2,
        ])
            ->actingAs($customer)
            ->test(Checkout::class)
            ->set('paymentMethods', [$product->store_id => 'xendit'])
            ->call('placeOrder');

        $component->assertRedirect(route('customer.orders'));

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'store_id' => $product->store_id,
            'total_price' => 155000,
            'shipping_cost' => 5000,
            'shipping_zone_label' => 'Kecamatan sama',
            'shipping_address' => $customer->address,
            'shipping_phone' => $customer->phone,
            'payment_method' => 'xendit',
            'payment_status' => 'unpaid',
            'status' => 'waiting_payment',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 75000,
        ]);

        $this->assertSame(3, $product->fresh()->stock);
        Event::assertDispatched(OrderPaymentUploaded::class);
    }

    public function test_cod_checkout_creates_unpaid_processing_order(): void
    {
        Event::fake([OrderPaymentUploaded::class]);

        $product = $this->createApprovedProduct();
        $customer = $this->createCustomer();

        Livewire::withQueryParams([
            'product_id' => $product->id,
            'qty' => 1,
        ])
            ->actingAs($customer)
            ->test(Checkout::class)
            ->set('paymentMethods', [$product->store_id => 'cod'])
            ->call('placeOrder')
            ->assertRedirect(route('customer.orders'));

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'store_id' => $product->store_id,
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'status' => 'processing',
        ]);

        $this->assertNull(Order::query()->where('customer_id', $customer->id)->value('paid_at'));
    }

    private function createCustomer(): User
    {
        $customer = User::factory()->create([
            'address' => 'Jalan Mawar, Kecamatan Balik Bukit, Kabupaten Lampung Barat',
            'phone' => '081234567890',
            'district_code' => '180404',
        ]);
        $customer->forceFill(['role' => 'customer'])->save();

        return $customer;
    }

    private function createApprovedProduct(): Product
    {
        $seller = User::factory()->create();
        $seller->forceFill(['role' => 'seller'])->save();

        $category = Category::create([
            'name' => 'Kopi',
            'slug' => 'kopi',
            'icon' => 'store',
        ]);

        $store = Store::create([
            'user_id' => $seller->id,
            'name' => 'Toko Kopi',
            'slug' => 'toko-kopi',
            'address' => 'Pasar Liwa, Kecamatan Balik Bukit, Kabupaten Lampung Barat',
            'district_code' => '180404',
            'status' => 'approved',
        ]);

        return Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Kopi Robusta',
            'slug' => 'kopi-robusta-checkout',
            'description' => 'Kopi robusta lokal.',
            'price' => 75000,
            'stock' => 5,
        ]);
    }
}
