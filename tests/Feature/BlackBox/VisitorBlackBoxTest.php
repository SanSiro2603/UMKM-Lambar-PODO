<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\ProductCatalog;
use App\Livewire\StoreCatalog;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class VisitorBlackBoxTest extends TestCase
{
    use RefreshDatabase;

    #[TestDox('PNG-001 Beranda dapat dibuka oleh pengunjung')]
    public function test_png_001_home_is_public(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Belanja Produk')
            ->assertSee('UMKM Terbaik')
            ->assertSee('Jelajahi Produk');
    }

    #[TestDox('PNG-002 Beranda hanya menampilkan toko dan produk approved')]
    public function test_png_002_home_only_shows_approved_stores_and_products(): void
    {
        $approved = $this->createStore('approved', 'Toko Disetujui');
        $pending = $this->createStore('pending', 'Toko Menunggu');
        $this->createProduct($approved, 'Produk Publik', 50000);
        $this->createProduct($pending, 'Produk Tersembunyi', 60000);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Toko Disetujui')
            ->assertSee('Produk Publik')
            ->assertDontSee('Toko Menunggu')
            ->assertDontSee('Produk Tersembunyi');
    }

    #[TestDox('PNG-003 Panduan tanpa parameter menampilkan panduan seller')]
    public function test_png_003_guide_defaults_to_seller(): void
    {
        $this->get(route('panduan'))
            ->assertOk()
            ->assertSee('Mulai Berjualan sebagai Seller');
    }

    #[TestDox('PNG-004 Parameter customer menampilkan panduan pelanggan')]
    public function test_png_004_customer_guide_can_be_selected(): void
    {
        $this->get(route('panduan', ['tab' => 'customer']))
            ->assertOk()
            ->assertSee('Belanja sebagai Customer');
    }

    #[TestDox('PNG-005 Parameter panduan tidak valid kembali ke seller')]
    public function test_png_005_invalid_guide_tab_falls_back_to_seller(): void
    {
        $this->get(route('panduan', ['tab' => 'tidak-valid']))
            ->assertOk()
            ->assertSee('Mulai Berjualan sebagai Seller');
    }

    #[TestDox('PNG-006 Katalog produk dapat dibuka')]
    public function test_png_006_product_catalog_is_public(): void
    {
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Semua Produk');
    }

    #[TestDox('PNG-007 Produk dapat dicari berdasarkan nama')]
    public function test_png_007_products_can_be_searched_by_name(): void
    {
        $store = $this->createStore();
        $this->createProduct($store, 'Kopi Robusta Pilihan', 50000);
        $this->createProduct($store, 'Keripik Pisang', 25000);

        Livewire::test(ProductCatalog::class)
            ->set('searchQuery', 'Robusta')
            ->assertSee('Kopi Robusta Pilihan')
            ->assertDontSee('Keripik Pisang');
    }

    #[TestDox('PNG-008 Produk dapat dicari berdasarkan deskripsi')]
    public function test_png_008_products_can_be_searched_by_description(): void
    {
        $store = $this->createStore();
        $this->createProduct($store, 'Kopi Lokal', 50000, 'Aroma cokelat khas');
        $this->createProduct($store, 'Kopi Lain', 45000, 'Aroma buah');

        Livewire::test(ProductCatalog::class)
            ->set('searchQuery', 'cokelat')
            ->assertSee('Kopi Lokal')
            ->assertDontSee('Kopi Lain');
    }

    #[TestDox('PNG-009 Produk dapat dicari berdasarkan nama toko')]
    public function test_png_009_products_can_be_searched_by_store_name(): void
    {
        $coffeeStore = $this->createStore('approved', 'Toko Bukit Kopi');
        $snackStore = $this->createStore('approved', 'Toko Camilan');
        $this->createProduct($coffeeStore, 'Produk Bukit', 50000);
        $this->createProduct($snackStore, 'Produk Camilan', 25000);

        Livewire::test(ProductCatalog::class)
            ->set('searchQuery', 'Bukit Kopi')
            ->assertSee('Produk Bukit')
            ->assertDontSee('Produk Camilan');
    }

    #[TestDox('PNG-010 Pencarian tanpa hasil menampilkan keadaan kosong')]
    public function test_png_010_unknown_search_shows_empty_state(): void
    {
        $store = $this->createStore();
        $this->createProduct($store, 'Kopi Robusta', 50000);

        Livewire::test(ProductCatalog::class)
            ->set('searchQuery', 'tidak-ada-produk')
            ->assertSee('Produk Tidak Ditemukan');
    }

    #[TestDox('PNG-011 Produk dapat difilter berdasarkan kategori')]
    public function test_png_011_products_can_be_filtered_by_category(): void
    {
        $store = $this->createStore();
        $coffee = $this->createCategory('Kopi');
        $snack = $this->createCategory('Camilan');
        $this->createProduct($store, 'Kopi Filter', 50000, category: $coffee);
        $this->createProduct($store, 'Keripik Filter', 25000, category: $snack);

        Livewire::test(ProductCatalog::class)
            ->set('categoryFilter', 'Kopi')
            ->assertSee('Kopi Filter')
            ->assertDontSee('Keripik Filter');
    }

    #[TestDox('PNG-012 Filter harga minimum membatasi produk')]
    public function test_png_012_minimum_price_filter_works(): void
    {
        $store = $this->createStore();
        $this->createProduct($store, 'Produk Murah', 20000);
        $this->createProduct($store, 'Produk Mahal', 80000);

        Livewire::test(ProductCatalog::class)
            ->set('minPrice', '50000')
            ->assertSee('Produk Mahal')
            ->assertDontSee('Produk Murah');
    }

    #[TestDox('PNG-013 Filter harga maksimum membatasi produk')]
    public function test_png_013_maximum_price_filter_works(): void
    {
        $store = $this->createStore();
        $this->createProduct($store, 'Produk Murah', 20000);
        $this->createProduct($store, 'Produk Mahal', 80000);

        Livewire::test(ProductCatalog::class)
            ->set('maxPrice', '50000')
            ->assertSee('Produk Murah')
            ->assertDontSee('Produk Mahal');
    }

    #[TestDox('PNG-014 Rentang harga minimum dan maksimum bekerja bersama')]
    public function test_png_014_price_range_filter_works(): void
    {
        $store = $this->createStore();
        $this->createProduct($store, 'Produk Rendah', 10000);
        $this->createProduct($store, 'Produk Tengah', 50000);
        $this->createProduct($store, 'Produk Tinggi', 100000);

        Livewire::test(ProductCatalog::class)
            ->set('minPrice', '30000')
            ->set('maxPrice', '70000')
            ->assertSee('Produk Tengah')
            ->assertDontSee('Produk Rendah')
            ->assertDontSee('Produk Tinggi');
    }

    #[TestDox('PNG-015 Produk dapat diurutkan dari yang terbaru')]
    public function test_png_015_products_sort_by_newest(): void
    {
        $store = $this->createStore();
        $old = $this->createProduct($store, 'Produk Lama', 40000);
        $new = $this->createProduct($store, 'Produk Baru', 50000);

        $products = Livewire::test(ProductCatalog::class)
            ->set('sort', 'terbaru')
            ->viewData('products');

        $this->assertSame([$new->id, $old->id], $products->pluck('id')->all());
    }

    #[TestDox('PNG-016 Produk dapat diurutkan dari harga termurah')]
    public function test_png_016_products_sort_by_lowest_price(): void
    {
        $store = $this->createStore();
        $expensive = $this->createProduct($store, 'Produk Mahal', 90000);
        $cheap = $this->createProduct($store, 'Produk Murah', 20000);

        $products = Livewire::test(ProductCatalog::class)
            ->set('sort', 'termurah')
            ->viewData('products');

        $this->assertSame([$cheap->id, $expensive->id], $products->pluck('id')->all());
    }

    #[TestDox('PNG-017 Produk dapat diurutkan dari harga termahal')]
    public function test_png_017_products_sort_by_highest_price(): void
    {
        $store = $this->createStore();
        $cheap = $this->createProduct($store, 'Produk Murah', 20000);
        $expensive = $this->createProduct($store, 'Produk Mahal', 90000);

        $products = Livewire::test(ProductCatalog::class)
            ->set('sort', 'termahal')
            ->viewData('products');

        $this->assertSame([$expensive->id, $cheap->id], $products->pluck('id')->all());
    }

    #[TestDox('PNG-018 Produk dapat diurutkan berdasarkan jumlah terjual')]
    public function test_png_018_products_sort_by_best_selling(): void
    {
        $store = $this->createStore();
        $lessSold = $this->createProduct($store, 'Produk Kurang Laris', 20000);
        $bestSeller = $this->createProduct($store, 'Produk Terlaris', 30000);
        $this->recordSale($lessSold, 1);
        $this->recordSale($bestSeller, 5);

        $products = Livewire::test(ProductCatalog::class)
            ->set('sort', 'terlaris')
            ->viewData('products');

        $this->assertSame([$bestSeller->id, $lessSold->id], $products->pluck('id')->all());
    }

    #[TestDox('PNG-019 Reset filter mengembalikan kondisi awal katalog')]
    public function test_png_019_filters_can_be_reset(): void
    {
        Livewire::test(ProductCatalog::class)
            ->set('searchQuery', 'kopi')
            ->set('categoryFilter', 'Kopi')
            ->set('minPrice', '10000')
            ->set('maxPrice', '90000')
            ->set('sort', 'termahal')
            ->call('resetFilters')
            ->assertSet('searchQuery', '')
            ->assertSet('categoryFilter', 'Semua')
            ->assertSet('minPrice', '')
            ->assertSet('maxPrice', '')
            ->assertSet('sort', 'terbaru');
    }

    #[TestDox('PNG-020 Pagination membatasi katalog menjadi 12 produk per halaman')]
    public function test_png_020_product_pagination_uses_twelve_items(): void
    {
        $store = $this->createStore();
        foreach (range(1, 13) as $number) {
            $this->createProduct($store, sprintf('Produk %02d', $number), 10000 + $number);
        }

        $component = Livewire::test(ProductCatalog::class);
        $this->assertCount(12, $component->viewData('products')->items());

        $component->call('setPage', 2);
        $this->assertCount(1, $component->viewData('products')->items());
    }

    #[TestDox('PNG-021 Detail produk approved dapat dibuka')]
    public function test_png_021_approved_product_detail_is_public(): void
    {
        $product = $this->createProduct($this->createStore(), 'Kopi Detail', 55000);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('Kopi Detail')
            ->assertSee('Rp 55.000');
    }

    #[TestDox('PNG-022 Slug produk tidak valid menghasilkan 404')]
    public function test_png_022_unknown_product_returns_not_found(): void
    {
        $this->get(route('products.show', 'produk-tidak-ada'))->assertNotFound();
    }

    #[TestDox('PNG-023 Produk dari toko belum approved tidak dapat dibuka')]
    public function test_png_023_unapproved_product_is_not_public(): void
    {
        $product = $this->createProduct($this->createStore('pending'), 'Produk Pending', 50000);

        $this->get(route('products.show', $product->slug))->assertNotFound();
    }

    #[TestDox('PNG-024 Katalog toko hanya menampilkan toko approved')]
    public function test_png_024_store_catalog_only_shows_approved_stores(): void
    {
        $this->createStore('approved', 'Toko Approved');
        $this->createStore('pending', 'Toko Pending');
        $this->createStore('rejected', 'Toko Rejected');

        Livewire::test(StoreCatalog::class)
            ->assertSee('Toko Approved')
            ->assertDontSee('Toko Pending')
            ->assertDontSee('Toko Rejected');
    }

    #[TestDox('PNG-025 Toko dapat dicari berdasarkan nama dan deskripsi')]
    public function test_png_025_stores_can_be_searched(): void
    {
        $this->createStore('approved', 'Toko Kopi Liwa', 'Menjual kopi pegunungan');
        $this->createStore('approved', 'Toko Keripik', 'Camilan pisang');

        Livewire::test(StoreCatalog::class)
            ->set('search', 'pegunungan')
            ->assertSee('Toko Kopi Liwa')
            ->assertDontSee('Toko Keripik');
    }

    #[TestDox('PNG-026 Toko dapat diurutkan berdasarkan nama')]
    public function test_png_026_stores_sort_by_name(): void
    {
        $zulu = $this->createStore('approved', 'Zulu Mart');
        $alpha = $this->createStore('approved', 'Alpha Mart');

        $stores = Livewire::test(StoreCatalog::class)
            ->set('sortBy', 'name')
            ->viewData('stores');

        $this->assertSame([$alpha->id, $zulu->id], $stores->pluck('id')->all());
    }

    #[TestDox('PNG-027 Toko dapat diurutkan berdasarkan jumlah produk')]
    public function test_png_027_stores_sort_by_product_count(): void
    {
        $few = $this->createStore('approved', 'Toko Sedikit');
        $many = $this->createStore('approved', 'Toko Banyak');
        $this->createProduct($few, 'Satu Produk', 10000);
        $this->createProduct($many, 'Produk A', 10000);
        $this->createProduct($many, 'Produk B', 20000);

        $stores = Livewire::test(StoreCatalog::class)
            ->set('sortBy', 'products')
            ->viewData('stores');

        $this->assertSame([$many->id, $few->id], $stores->pluck('id')->all());
    }

    #[TestDox('PNG-028 Pencarian toko tanpa hasil menampilkan keadaan kosong')]
    public function test_png_028_unknown_store_search_shows_empty_state(): void
    {
        $this->createStore('approved', 'Toko Kopi');

        Livewire::test(StoreCatalog::class)
            ->set('search', 'tidak-ada-toko')
            ->assertSee('Toko Tidak Ditemukan');
    }

    #[TestDox('PNG-029 Detail toko approved beserta produknya dapat dibuka')]
    public function test_png_029_approved_store_detail_is_public(): void
    {
        $store = $this->createStore('approved', 'Toko Detail');
        $this->createProduct($store, 'Produk Toko Detail', 45000);

        $this->get(route('stores.show', $store->slug))
            ->assertOk()
            ->assertSee('Toko Detail')
            ->assertSee('Produk Toko Detail');
    }

    #[TestDox('PNG-030 Toko tidak valid atau belum approved menghasilkan 404')]
    public function test_png_030_unknown_and_unapproved_store_return_not_found(): void
    {
        $pending = $this->createStore('pending', 'Toko Belum Aktif');

        $this->get(route('stores.show', 'toko-tidak-ada'))->assertNotFound();
        $this->get(route('stores.show', $pending->slug))->assertNotFound();
    }

    #[TestDox('PNG-031 Pengunjung diarahkan ke login dari halaman pelanggan')]
    public function test_png_031_guest_is_redirected_from_customer_pages(): void
    {
        foreach (['checkout', 'customer.dashboard', 'customer.orders'] as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('login'));
        }
    }

    #[TestDox('PNG-032 Pengunjung diarahkan ke login dari halaman seller dan admin')]
    public function test_png_032_guest_is_redirected_from_seller_and_admin_pages(): void
    {
        foreach (['seller.dashboard', 'seller.products', 'admin.dashboard', 'admin.categories'] as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('login'));
        }
    }

    private function createCategory(string $name = 'Produk Lokal'): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => str($name)->slug().'-'.str()->lower(str()->random(5)),
            'icon' => 'store',
        ]);
    }

    private function createStore(
        string $status = 'approved',
        ?string $name = null,
        string $description = 'Deskripsi toko lokal'
    ): Store {
        $seller = User::factory()->create();
        $seller->forceFill(['role' => 'seller'])->save();
        $name ??= 'Toko '.str()->upper(str()->random(6));

        return Store::create([
            'user_id' => $seller->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.str()->lower(str()->random(5)),
            'description' => $description,
            'address' => 'Kabupaten Lampung Barat',
            'status' => $status,
        ]);
    }

    private function createProduct(
        Store $store,
        string $name,
        int $price,
        string $description = 'Deskripsi produk lokal',
        ?Category $category = null
    ): Product {
        $category ??= $this->createCategory();

        return Product::create([
            'store_id' => $store->id,
            'category_id' => $category->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.str()->lower(str()->random(5)),
            'description' => $description,
            'price' => $price,
            'stock' => 10,
        ]);
    }

    private function recordSale(Product $product, int $quantity): void
    {
        $customer = User::factory()->create([
            'address' => 'Kabupaten Lampung Barat',
            'phone' => '081234567890',
        ]);
        $customer->forceFill(['role' => 'customer'])->save();

        $order = Order::create([
            'order_code' => 'ORD-'.str()->upper(str()->random(10)),
            'customer_id' => $customer->id,
            'store_id' => $product->store_id,
            'total_price' => $product->price * $quantity,
            'shipping_cost' => 5000,
            'shipping_address' => $customer->address,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'status' => 'delivered',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'qty' => $quantity,
            'price' => $product->price,
        ]);
    }
}
