<?php

namespace Tests\Feature\BlackBox;

use App\Events\OrderPaymentUploaded;
use App\Livewire\CartPage;
use App\Livewire\Checkout;
use App\Livewire\Customer\Dashboard;
use App\Livewire\Customer\OrderDetails;
use App\Livewire\Customer\Orders;
use App\Livewire\ProductDetail;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use App\Services\ActiveOrderShippingSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class CustomerShoppingBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createBlackBoxRegions();
    }

    #[TestDox('PLG-DSH-001 Dashboard hanya menghitung pesanan milik pelanggan aktif')]
    public function test_plg_dsh_001_dashboard_uses_customer_own_orders(): void
    {
        $customer = $this->makeBlackBoxUser();
        $other = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $this->makeBlackBoxOrder($customer, $product, 'processing');
        $this->makeBlackBoxOrder($customer, $product, 'delivered');
        $this->makeBlackBoxOrder($other, $product, 'processing');

        $component = Livewire::actingAs($customer)->test(Dashboard::class);
        $this->assertSame(1, $component->viewData('pesananAktif'));
        $this->assertSame(1, $component->viewData('pesananSelesai'));
        $this->assertCount(2, $component->viewData('recentOrders'));
    }

    #[TestDox('PLG-DSH-002 Edit dan batal alamat mengembalikan data awal')]
    public function test_plg_dsh_002_address_edit_can_be_cancelled(): void
    {
        $customer = $this->makeBlackBoxUser();

        Livewire::actingAs($customer)
            ->test(Dashboard::class)
            ->call('editAddress')
            ->assertSet('isEditingAddress', true)
            ->set('detailAddress', 'Alamat Sementara')
            ->call('cancelEdit')
            ->assertSet('isEditingAddress', false)
            ->assertSet('detailAddress', 'Jalan Uji No. 1');
    }

    #[TestDox('PLG-DSH-003 Field alamat kosong dan terlalu pendek ditolak')]
    public function test_plg_dsh_003_address_fields_are_validated(): void
    {
        $customer = $this->makeBlackBoxUser();

        Livewire::actingAs($customer)
            ->test(Dashboard::class)
            ->set('selectedDistrictCode', '')
            ->set('selectedVillageCode', '')
            ->set('detailAddress', '')
            ->call('updateAddress')
            ->assertHasErrors(['selectedDistrictCode', 'selectedVillageCode', 'detailAddress']);

        Livewire::actingAs($customer)
            ->test(Dashboard::class)
            ->set('selectedDistrictCode', '180419')
            ->set('selectedVillageCode', '1804192001')
            ->set('detailAddress', 'Jl')
            ->call('updateAddress')
            ->assertHasErrors(['detailAddress' => 'min']);
    }

    #[TestDox('PLG-DSH-004 Kombinasi kecamatan dan desa tidak terdaftar ditolak')]
    public function test_plg_dsh_004_unknown_address_region_is_rejected(): void
    {
        $customer = $this->makeBlackBoxUser();
        $originalAddress = $customer->address;

        Livewire::actingAs($customer)
            ->test(Dashboard::class)
            ->set('selectedDistrictCode', '180499')
            ->set('selectedVillageCode', '1804999999')
            ->set('detailAddress', 'Jalan Tidak Dikenal')
            ->call('updateAddress')
            ->assertSee('Data wilayah tidak valid.');

        $this->assertSame($originalAddress, $customer->fresh()->address);
    }

    #[TestDox('PLG-DSH-005 Alamat valid diperbarui dengan format lengkap')]
    public function test_plg_dsh_005_valid_address_is_saved(): void
    {
        $customer = $this->makeBlackBoxUser();
        $sync = Mockery::mock(ActiveOrderShippingSyncService::class);
        $sync->shouldReceive('sync')->once()->andReturn([
            'updated' => 0, 'invoice_resets' => 0, 'skipped' => 0, 'failed' => 0, 'failures' => [],
        ]);
        $this->app->instance(ActiveOrderShippingSyncService::class, $sync);

        Livewire::actingAs($customer)
            ->test(Dashboard::class)
            ->set('selectedDistrictCode', '180404')
            ->set('selectedVillageCode', '1804042001')
            ->set('detailAddress', 'Jalan Baru Nomor 20')
            ->call('updateAddress')
            ->assertHasNoErrors()
            ->assertSet('isEditingAddress', false);

        $customer->refresh();
        $this->assertSame('180404', $customer->district_code);
        $this->assertStringContainsString('PASAR LIWA', $customer->address);
        $this->assertStringContainsString('BALIK BUKIT', $customer->address);
    }

    #[TestDox('PLG-CRT-001 Pengunjung diarahkan ke login saat membuka keranjang')]
    public function test_plg_crt_001_guest_cart_redirects_to_login(): void
    {
        $this->get(route('cart'))->assertRedirect(route('login'));
    }

    #[TestDox('PLG-CRT-002 Keranjang kosong menampilkan keadaan kosong')]
    public function test_plg_crt_002_empty_cart_is_shown(): void
    {
        $customer = $this->makeBlackBoxUser();
        Livewire::actingAs($customer)->test(CartPage::class)->assertSee('Keranjang Kosong');
    }

    #[TestDox('PLG-CRT-003 Pengunjung diminta login saat menambah produk')]
    public function test_plg_crt_003_guest_add_to_cart_requires_login(): void
    {
        $product = $this->makeBlackBoxProduct();
        Livewire::test(ProductDetail::class, ['slug' => $product->slug])
            ->call('addToCart')
            ->assertDispatched('login-required');
    }

    #[TestDox('PLG-CRT-004 Produk baru dapat ditambahkan ke keranjang')]
    public function test_plg_crt_004_customer_can_add_new_cart_item(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();

        Livewire::actingAs($customer)->test(ProductDetail::class, ['slug' => $product->slug])
            ->set('qty', 2)->call('addToCart')->assertDispatched('toast');

        $this->assertDatabaseHas('cart_items', ['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 2]);
    }

    #[TestDox('PLG-CRT-005 Produk yang sama menambah kuantitas item lama')]
    public function test_plg_crt_005_same_product_increments_existing_item(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 2]);

        Livewire::actingAs($customer)->test(ProductDetail::class, ['slug' => $product->slug])
            ->set('qty', 3)->call('addToCart');

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 5]);
    }

    #[TestDox('PLG-CRT-006 Produk habis tidak dapat ditambahkan ke keranjang')]
    public function test_plg_crt_006_out_of_stock_product_is_rejected(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct(attributes: ['stock' => 0]);

        Livewire::actingAs($customer)->test(ProductDetail::class, ['slug' => $product->slug])
            ->call('addToCart')->assertDispatched('toast');

        $this->assertDatabaseCount('cart_items', 0);
    }

    #[TestDox('PLG-CRT-007 Kuantitas tambahan dibatasi oleh stok')]
    public function test_plg_crt_007_add_to_cart_is_clamped_to_stock(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct(attributes: ['stock' => 3]);

        Livewire::actingAs($customer)->test(ProductDetail::class, ['slug' => $product->slug])
            ->set('qty', 8)->call('addToCart');

        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'qty' => 3]);
    }

    #[TestDox('PLG-CRT-008 Keranjang menghitung item terpilih dan total harga')]
    public function test_plg_crt_008_selected_count_and_total_are_calculated(): void
    {
        $customer = $this->makeBlackBoxUser();
        $store = $this->makeBlackBoxStore();
        $first = $this->makeBlackBoxProduct($store, ['price' => 20000]);
        $second = $this->makeBlackBoxProduct($store, ['price' => 50000]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $first->id, 'qty' => 2, 'selected' => true]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $second->id, 'qty' => 1, 'selected' => false]);

        $component = Livewire::actingAs($customer)->test(CartPage::class);
        $this->assertSame(2, $component->get('selectedCount'));
        $this->assertSame(40000, $component->get('selectedTotal'));
        $this->assertCount(1, $component->get('groupedBySeller'));
    }

    #[TestDox('PLG-CRT-009 Perubahan kuantitas dibatasi minimal satu dan maksimal stok')]
    public function test_plg_crt_009_quantity_update_obeys_boundaries(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct(attributes: ['stock' => 4]);
        $item = CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 2]);
        $component = Livewire::actingAs($customer)->test(CartPage::class);

        $component->call('updateQty', $item->id, 0);
        $this->assertSame(1, $item->fresh()->qty);
        $component->call('updateQty', $item->id, 10)->assertDispatched('toast');
        $this->assertSame(4, $item->fresh()->qty);
    }

    #[TestDox('PLG-CRT-010 Pelanggan tidak dapat mengubah item keranjang pengguna lain')]
    public function test_plg_crt_010_customer_cannot_update_another_cart(): void
    {
        $customer = $this->makeBlackBoxUser();
        $other = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $item = CartItem::create(['user_id' => $other->id, 'product_id' => $product->id, 'qty' => 2]);

        Livewire::actingAs($customer)->test(CartPage::class)->call('updateQty', $item->id, 9);
        $this->assertSame(2, $item->fresh()->qty);
    }

    #[TestDox('PLG-CRT-011 Item keranjang dapat dipilih dan batal dipilih')]
    public function test_plg_crt_011_cart_item_selection_can_be_toggled(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $item = CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 1, 'selected' => true]);
        $component = Livewire::actingAs($customer)->test(CartPage::class);

        $component->call('toggleSelected', $item->id);
        $this->assertFalse($item->fresh()->selected);
        $component->call('toggleSelected', $item->id);
        $this->assertTrue($item->fresh()->selected);
    }

    #[TestDox('PLG-CRT-012 Semua produk dalam satu toko dapat dipilih atau dibatalkan')]
    public function test_plg_crt_012_store_items_can_be_selected_and_deselected_together(): void
    {
        $customer = $this->makeBlackBoxUser();
        $store = $this->makeBlackBoxStore();
        $first = $this->makeBlackBoxProduct($store);
        $second = $this->makeBlackBoxProduct($store);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $first->id, 'qty' => 1, 'selected' => false]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $second->id, 'qty' => 1, 'selected' => false]);
        $component = Livewire::actingAs($customer)->test(CartPage::class);

        $component->call('selectAllForSeller', $store->name);
        $this->assertSame(2, CartItem::query()->where('selected', true)->count());
        $component->call('deselectAllForSeller', $store->name);
        $this->assertSame(0, CartItem::query()->where('selected', true)->count());
    }

    #[TestDox('PLG-CRT-013 Pelanggan dapat menghapus item keranjangnya')]
    public function test_plg_crt_013_customer_can_remove_own_cart_item(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $item = CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 1]);

        Livewire::actingAs($customer)->test(CartPage::class)->call('removeItem', $item->id)->assertDispatched('toast');
        $this->assertModelMissing($item);
    }

    #[TestDox('PLG-CRT-014 Pelanggan tidak dapat menghapus item pengguna lain')]
    public function test_plg_crt_014_customer_cannot_remove_another_cart_item(): void
    {
        $customer = $this->makeBlackBoxUser();
        $other = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $item = CartItem::create(['user_id' => $other->id, 'product_id' => $product->id, 'qty' => 1]);

        Livewire::actingAs($customer)->test(CartPage::class)->call('removeItem', $item->id);
        $this->assertModelExists($item);
    }

    #[TestDox('PLG-CRT-015 Checkout tanpa produk terpilih ditolak')]
    public function test_plg_crt_015_checkout_without_selection_is_rejected(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 1, 'selected' => false]);

        Livewire::actingAs($customer)->test(CartPage::class)->call('checkout')->assertDispatched('toast')->assertNoRedirect();
    }

    #[TestDox('PLG-CRT-016 Checkout dengan produk terpilih menuju halaman checkout')]
    public function test_plg_crt_016_selected_cart_can_continue_to_checkout(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 1, 'selected' => true]);

        Livewire::actingAs($customer)->test(CartPage::class)->call('checkout')->assertRedirect(route('checkout'));
    }

    #[TestDox('PLG-CKO-001 Pelanggan tanpa alamat dialihkan ke dashboard')]
    public function test_plg_cko_001_checkout_requires_customer_address(): void
    {
        $customer = $this->makeBlackBoxUser(attributes: ['address' => null]);
        $this->actingAs($customer)->get(route('checkout'))->assertRedirect(route('customer.dashboard'));
    }

    #[TestDox('PLG-CKO-002 Checkout memuat alamat telepon produk dan ongkir')]
    public function test_plg_cko_002_checkout_loads_customer_and_shipping_summary(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct(attributes: ['price' => 50000]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 2, 'selected' => true]);

        Livewire::actingAs($customer)->test(Checkout::class)
            ->assertSet('shippingAddress', $customer->address)
            ->assertSet('shippingPhone', $customer->phone)
            ->assertSee('Rp 100.000')
            ->assertSee('Rp 5.000');
    }

    #[TestDox('PLG-CKO-003 Alamat checkout kosong pendek atau di luar Lampung Barat ditolak')]
    public function test_plg_cko_003_checkout_address_is_validated(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 1]);

        Livewire::actingAs($customer)->test(Checkout::class)
            ->set('shippingAddress', '')
            ->call('placeOrder')
            ->assertHasErrors(['shippingAddress' => 'required']);

        Livewire::actingAs($customer)->test(Checkout::class)
            ->set('shippingAddress', 'Jalan Jakarta Nomor 10')
            ->call('placeOrder')
            ->assertSee('hanya melayani pengiriman ke wilayah Kabupaten Lampung Barat');
    }

    #[TestDox('PLG-CKO-004 Nomor telepon checkout divalidasi')]
    public function test_plg_cko_004_checkout_phone_is_validated(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 1]);

        Livewire::actingAs($customer)->test(Checkout::class)
            ->set('shippingPhone', 'abc')
            ->call('placeOrder')
            ->assertHasErrors(['shippingPhone']);
    }

    #[TestDox('PLG-CKO-005 Checkout tanpa item terpilih tidak membuat pesanan')]
    public function test_plg_cko_005_checkout_without_items_creates_no_order(): void
    {
        $customer = $this->makeBlackBoxUser();

        Livewire::actingAs($customer)->test(Checkout::class)->call('placeOrder')->assertSee('Tidak ada produk yang dipilih');
        $this->assertDatabaseCount('orders', 0);
    }

    #[TestDox('PLG-CKO-006 Checkout lintas toko membuat pesanan terpisah')]
    public function test_plg_cko_006_multi_store_checkout_splits_orders(): void
    {
        Event::fake([OrderPaymentUploaded::class]);
        $customer = $this->makeBlackBoxUser();
        $first = $this->makeBlackBoxProduct($this->makeBlackBoxStore(), ['stock' => 5]);
        $second = $this->makeBlackBoxProduct($this->makeBlackBoxStore(), ['stock' => 5]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $first->id, 'qty' => 1, 'selected' => true]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $second->id, 'qty' => 2, 'selected' => true]);

        Livewire::actingAs($customer)->test(Checkout::class)
            ->set('paymentMethods', [$first->store_id => 'cod', $second->store_id => 'cod'])
            ->call('placeOrder')
            ->assertRedirect(route('customer.orders'));

        $this->assertSame(2, Order::query()->where('customer_id', $customer->id)->count());
        $this->assertSame(4, $first->fresh()->stock);
        $this->assertSame(3, $second->fresh()->stock);
    }

    #[TestDox('PLG-CKO-007 Stok tidak mencukupi menggagalkan seluruh transaksi')]
    public function test_plg_cko_007_insufficient_stock_rolls_back_checkout(): void
    {
        Event::fake([OrderPaymentUploaded::class]);
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct(attributes: ['stock' => 1]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 3, 'selected' => true]);

        Livewire::actingAs($customer)->test(Checkout::class)->call('placeOrder');

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'qty' => 3]);
    }

    #[TestDox('PLG-CKO-008 Pesanan aktif yang identik mencegah checkout duplikat')]
    public function test_plg_cko_008_duplicate_active_order_is_blocked(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        CartItem::create(['user_id' => $customer->id, 'product_id' => $product->id, 'qty' => 1, 'selected' => true]);
        $this->makeBlackBoxOrder($customer, $product, 'processing');

        Livewire::actingAs($customer)->test(Checkout::class)->assertRedirect(route('customer.orders'));
        $this->assertDatabaseCount('orders', 1);
    }

    #[TestDox('PLG-CKO-009 Checkout menghapus item terpilih dan mempertahankan item lain')]
    public function test_plg_cko_009_checkout_only_removes_purchased_cart_items(): void
    {
        Event::fake([OrderPaymentUploaded::class]);
        $customer = $this->makeBlackBoxUser();
        $selected = $this->makeBlackBoxProduct();
        $unselected = $this->makeBlackBoxProduct();
        CartItem::create(['user_id' => $customer->id, 'product_id' => $selected->id, 'qty' => 1, 'selected' => true]);
        CartItem::create(['user_id' => $customer->id, 'product_id' => $unselected->id, 'qty' => 1, 'selected' => false]);

        Livewire::actingAs($customer)->test(Checkout::class)
            ->set('paymentMethods', [$selected->store_id => 'cod'])
            ->call('placeOrder');

        $this->assertDatabaseMissing('cart_items', ['product_id' => $selected->id]);
        $this->assertDatabaseHas('cart_items', ['product_id' => $unselected->id]);
    }

    #[TestDox('PLG-ORD-001 Daftar pesanan hanya menampilkan milik pelanggan aktif')]
    public function test_plg_ord_001_order_list_is_scoped_to_customer(): void
    {
        $customer = $this->makeBlackBoxUser();
        $other = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $own = $this->makeBlackBoxOrder($customer, $product);
        $foreign = $this->makeBlackBoxOrder($other, $product);

        Livewire::actingAs($customer)->test(Orders::class)
            ->assertSee($own->order_code)
            ->assertDontSee($foreign->order_code);
    }

    #[TestDox('PLG-ORD-002 Filter status pesanan menampilkan status yang dipilih')]
    public function test_plg_ord_002_order_status_tabs_filter_results(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $waiting = $this->makeBlackBoxOrder($customer, $product, 'waiting_payment');
        $shipped = $this->makeBlackBoxOrder($customer, $product, 'shipped');

        Livewire::actingAs($customer)->test(Orders::class)
            ->call('selectTab', 'menunggu')
            ->assertSee($waiting->order_code)
            ->assertDontSee($shipped->order_code)
            ->call('selectTab', 'shipped')
            ->assertSee($shipped->order_code)
            ->assertDontSee($waiting->order_code);
    }

    #[TestDox('PLG-ORD-003 Daftar pesanan menggunakan pagination 20 item')]
    public function test_plg_ord_003_orders_are_paginated_by_twenty(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        foreach (range(1, 21) as $ignored) {
            $this->makeBlackBoxOrder($customer, $product, 'delivered');
        }

        $component = Livewire::actingAs($customer)->test(Orders::class);
        $this->assertCount(20, $component->viewData('orders')->items());
        $secondPage = Livewire::withQueryParams(['page' => 2])->actingAs($customer)->test(Orders::class);
        $this->assertCount(1, $secondPage->viewData('orders')->items());
    }

    #[TestDox('PLG-ORD-004 Pelanggan dapat membuka detail pesanannya sendiri')]
    public function test_plg_ord_004_customer_can_open_own_order_detail(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $order = $this->makeBlackBoxOrder($customer, $product);

        $this->actingAs($customer)->get(route('customer.orders.show', $order->id))
            ->assertOk()->assertSee($order->order_code)->assertSee($product->name);
    }

    #[TestDox('PLG-ORD-005 Pelanggan tidak dapat membuka pesanan pelanggan lain')]
    public function test_plg_ord_005_customer_cannot_open_another_order(): void
    {
        $customer = $this->makeBlackBoxUser();
        $other = $this->makeBlackBoxUser();
        $order = $this->makeBlackBoxOrder($other, $this->makeBlackBoxProduct());

        $this->actingAs($customer)->get(route('customer.orders.show', $order->id))->assertNotFound();
    }

    #[TestDox('PLG-ORD-006 Pembatalan waiting payment mengembalikan stok satu kali')]
    public function test_plg_ord_006_cancellation_restores_stock_once(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct(attributes: ['stock' => 5]);
        $order = $this->makeBlackBoxOrder($customer, $product, 'waiting_payment', ['payment_method' => 'xendit'], 2);
        $component = Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id]);

        $component->call('cancelOrder');
        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('failed', $order->fresh()->payment_status);
        $this->assertSame(7, $product->fresh()->stock);
        $component->call('cancelOrder');
        $this->assertSame(7, $product->fresh()->stock);
    }

    #[TestDox('PLG-ORD-007 Pesanan selain waiting payment tidak dapat dibatalkan')]
    public function test_plg_ord_007_non_waiting_order_cannot_be_cancelled(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct(attributes: ['stock' => 5]);
        $order = $this->makeBlackBoxOrder($customer, $product, 'processing');

        Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id])->call('cancelOrder');
        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame(5, $product->fresh()->stock);
    }

    #[TestDox('PLG-ORD-008 Pesanan shipped dapat dikonfirmasi diterima')]
    public function test_plg_ord_008_shipped_order_can_be_confirmed_received(): void
    {
        $customer = $this->makeBlackBoxUser();
        $order = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct(), 'shipped');

        Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id])->call('confirmReceived');
        $this->assertSame('delivered', $order->fresh()->status);
    }

    #[TestDox('PLG-ORD-009 Pesanan yang belum shipped tidak dapat dikonfirmasi')]
    public function test_plg_ord_009_non_shipped_order_cannot_be_confirmed(): void
    {
        $customer = $this->makeBlackBoxUser();
        $order = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct(), 'processing');

        Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id])->call('confirmReceived');
        $this->assertSame('processing', $order->fresh()->status);
    }

    #[TestDox('PLG-RAT-001 Pesanan selesai dapat diberi rating dan memperbarui rata-rata produk')]
    public function test_plg_rat_001_delivered_product_can_be_rated(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $order = $this->makeBlackBoxOrder($customer, $product, 'delivered');

        Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id])
            ->set("ratingInputs.{$product->id}", 5)
            ->set("commentInputs.{$product->id}", 'Produk sangat baik')
            ->call('submitRating', $product->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ratings', ['user_id' => $customer->id, 'product_id' => $product->id, 'rating' => 5]);
        $this->assertSame(5, $product->fresh()->rating);
    }

    #[TestDox('PLG-RAT-002 Rating di luar rentang 1 sampai 5 ditolak')]
    public function test_plg_rat_002_rating_boundaries_are_validated(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $order = $this->makeBlackBoxOrder($customer, $product, 'delivered');

        Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id])
            ->set("ratingInputs.{$product->id}", 0)
            ->call('submitRating', $product->id)
            ->assertHasErrors(["ratingInputs.{$product->id}" => 'min']);
        Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id])
            ->set("ratingInputs.{$product->id}", 6)
            ->call('submitRating', $product->id)
            ->assertHasErrors(["ratingInputs.{$product->id}" => 'max']);
    }

    #[TestDox('PLG-RAT-003 Pesanan yang belum selesai tidak dapat diberi rating')]
    public function test_plg_rat_003_unfinished_order_cannot_be_rated(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $order = $this->makeBlackBoxOrder($customer, $product, 'processing');

        Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id])
            ->set("ratingInputs.{$product->id}", 5)->call('submitRating', $product->id);
        $this->assertDatabaseCount('ratings', 0);
    }

    #[TestDox('PLG-RAT-004 Produk di luar pesanan tidak dapat diberi rating')]
    public function test_plg_rat_004_product_outside_order_cannot_be_rated(): void
    {
        $customer = $this->makeBlackBoxUser();
        $ordered = $this->makeBlackBoxProduct();
        $foreign = $this->makeBlackBoxProduct();
        $order = $this->makeBlackBoxOrder($customer, $ordered, 'delivered');

        Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id])
            ->set("ratingInputs.{$foreign->id}", 5)->call('submitRating', $foreign->id);
        $this->assertDatabaseMissing('ratings', ['product_id' => $foreign->id]);
    }

    #[TestDox('PLG-RAT-005 Produk yang sama hanya dapat dinilai sekali per pesanan')]
    public function test_plg_rat_005_duplicate_rating_is_rejected(): void
    {
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $order = $this->makeBlackBoxOrder($customer, $product, 'delivered');
        Rating::create(['user_id' => $customer->id, 'product_id' => $product->id, 'order_id' => $order->id, 'rating' => 4]);

        Livewire::actingAs($customer)->test(OrderDetails::class, ['id' => $order->id])
            ->set("ratingInputs.{$product->id}", 5)->call('submitRating', $product->id);
        $this->assertSame(1, Rating::query()->where('order_id', $order->id)->count());
    }
}
