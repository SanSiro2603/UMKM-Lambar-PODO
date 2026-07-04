<?php

namespace Tests\Feature;

use App\Livewire\ProductDetail;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductDetailBuyNowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_buy_now_dispatches_login_required_event(): void
    {
        $product = $this->createApprovedProduct();

        Livewire::test(ProductDetail::class, ['slug' => $product->slug])
            ->call('buyNow')
            ->assertDispatched('login-required');
    }

    public function test_customer_buy_now_redirects_to_checkout_with_product_and_qty(): void
    {
        $product = $this->createApprovedProduct();
        $customer = $this->createCustomer();

        Livewire::actingAs($customer)
            ->test(ProductDetail::class, ['slug' => $product->slug])
            ->set('qty', 3)
            ->call('buyNow')
            ->assertRedirect(route('checkout', [
                'product_id' => $product->id,
                'qty' => 3,
            ]));
    }

    public function test_buy_now_with_empty_stock_dispatches_error_toast(): void
    {
        $product = $this->createApprovedProduct(stock: 0);
        $customer = $this->createCustomer();

        Livewire::actingAs($customer)
            ->test(ProductDetail::class, ['slug' => $product->slug])
            ->call('buyNow')
            ->assertDispatched('toast');
    }

    public function test_buy_now_clamps_quantity_to_available_stock(): void
    {
        $product = $this->createApprovedProduct(stock: 2);
        $customer = $this->createCustomer();

        Livewire::actingAs($customer)
            ->test(ProductDetail::class, ['slug' => $product->slug])
            ->set('qty', 5)
            ->call('buyNow')
            ->assertSet('qty', 2)
            ->assertRedirect(route('checkout', [
                'product_id' => $product->id,
                'qty' => 2,
            ]));
    }

    private function createCustomer(): User
    {
        $customer = User::factory()->create([
            'address' => 'Pekon Rigis Jaya, Kec. Air Hitam, Kabupaten Lampung Barat',
        ]);
        $customer->forceFill(['role' => 'customer'])->save();

        return $customer;
    }

    private function createApprovedProduct(int $stock = 10): Product
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
            'status' => 'approved',
        ]);

        return Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => 'Kopi Robusta',
            'slug' => 'kopi-robusta-' . $stock,
            'description' => 'Kopi robusta lokal.',
            'price' => 75000,
            'stock' => $stock,
        ]);
    }
}
