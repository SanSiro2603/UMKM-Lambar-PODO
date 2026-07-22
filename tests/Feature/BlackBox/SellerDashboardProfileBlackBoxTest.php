<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\Seller\Dashboard;
use App\Livewire\Seller\NotificationBell;
use App\Livewire\Seller\StoreProfile;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class SellerDashboardProfileBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    #[TestDox('PJL-STA-001 Seller pending melihat halaman masa tunggu persetujuan')]
    public function test_pjl_sta_001_pending_seller_sees_review_page(): void
    {
        $store = $this->makeBlackBoxStore('pending');
        Livewire::actingAs($store->user)->test(Dashboard::class)
            ->assertSee('sedang dalam proses verifikasi');
    }

    #[TestDox('PJL-STA-002 Seller pending tidak dapat membuka fitur khusus toko approved')]
    public function test_pjl_sta_002_pending_seller_is_redirected_from_protected_features(): void
    {
        $store = $this->makeBlackBoxStore('pending');
        $this->actingAs($store->user)->get(route('seller.products'))
            ->assertRedirect(route('seller.dashboard'));
    }

    #[TestDox('PJL-STA-003 Seller approved dapat membuka dashboard dan fitur toko')]
    public function test_pjl_sta_003_approved_seller_can_access_features(): void
    {
        $store = $this->makeBlackBoxStore('approved');
        $this->actingAs($store->user)->get(route('seller.dashboard'))->assertOk();
        $this->actingAs($store->user)->get(route('seller.products'))->assertOk();
    }

    #[TestDox('PJL-STA-004 Seller rejected dikeluarkan dari sesi dan diarahkan ke login')]
    public function test_pjl_sta_004_rejected_seller_is_logged_out(): void
    {
        $store = $this->makeBlackBoxStore('rejected');
        $this->actingAs($store->user)->get(route('seller.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[TestDox('PJL-DSH-001 Statistik dashboard hanya menghitung data toko aktif')]
    public function test_pjl_dsh_001_dashboard_stats_are_scoped_to_active_store(): void
    {
        $store = $this->makeBlackBoxStore();
        $product = $this->makeBlackBoxProduct($store);
        $customerA = $this->makeBlackBoxUser('customer');
        $customerB = $this->makeBlackBoxUser('customer');
        $this->makeBlackBoxOrder($customerA, $product, 'waiting_payment', ['total_price' => 55000]);
        $this->makeBlackBoxOrder($customerB, $product, 'delivered', ['total_price' => 105000]);

        $otherProduct = $this->makeBlackBoxProduct();
        $this->makeBlackBoxOrder($customerA, $otherProduct, 'delivered', ['total_price' => 999999]);

        Livewire::actingAs($store->user)->test(Dashboard::class)
            ->assertViewHas('totalProducts', 1)
            ->assertViewHas('newOrders', 1)
            ->assertViewHas('revenue', 105000)
            ->assertViewHas('totalCustomers', 2);
    }

    #[TestDox('PJL-DSH-002 Pendapatan dashboard mengecualikan waiting payment dan cancelled')]
    public function test_pjl_dsh_002_revenue_uses_only_active_paid_flow_statuses(): void
    {
        $store = $this->makeBlackBoxStore();
        $product = $this->makeBlackBoxProduct($store);
        $customer = $this->makeBlackBoxUser();
        foreach (['waiting_payment', 'cancelled'] as $status) {
            $this->makeBlackBoxOrder($customer, $product, $status, ['total_price' => 90000]);
        }
        foreach (['processing', 'shipped', 'delivered'] as $status) {
            $this->makeBlackBoxOrder($customer, $product, $status, ['total_price' => 10000]);
        }

        Livewire::actingAs($store->user)->test(Dashboard::class)->assertViewHas('revenue', 30000);
    }

    #[TestDox('PJL-DSH-003 Dashboard menampilkan produk stok rendah milik toko')]
    public function test_pjl_dsh_003_low_stock_list_is_scoped_and_ordered(): void
    {
        $store = $this->makeBlackBoxStore();
        $this->makeBlackBoxProduct($store, ['name' => 'Stok Lima', 'slug' => 'stok-lima', 'stock' => 5]);
        $this->makeBlackBoxProduct($store, ['name' => 'Stok Nol', 'slug' => 'stok-nol', 'stock' => 0]);
        $this->makeBlackBoxProduct($store, ['name' => 'Stok Banyak', 'slug' => 'stok-banyak', 'stock' => 6]);

        Livewire::actingAs($store->user)->test(Dashboard::class)
            ->assertSeeInOrder(['Stok Nol', 'Stok Lima'])->assertDontSee('Stok Banyak');
    }

    #[TestDox('PJL-DSH-004 Ringkasan transaksi split hanya menggunakan transaksi seller aktif')]
    public function test_pjl_dsh_004_split_transaction_summary_is_scoped(): void
    {
        $store = $this->makeBlackBoxStore();
        $customer = $this->makeBlackBoxUser();
        $order = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($store), 'delivered');
        Transaction::create(['order_id' => $order->id, 'seller_id' => $store->user_id, 'total_amount' => 100000, 'seller_amount' => 95000, 'status' => 'disbursed']);

        $other = $this->makeBlackBoxStore();
        $otherOrder = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($other), 'delivered');
        Transaction::create(['order_id' => $otherOrder->id, 'seller_id' => $other->user_id, 'total_amount' => 500000, 'seller_amount' => 475000, 'status' => 'disbursed']);

        Livewire::actingAs($store->user)->test(Dashboard::class)->assertViewHas('totalSalesSplit', 95000);
    }

    #[TestDox('PJL-NTF-001 Notifikasi seller hanya memuat pesanan tokonya sendiri')]
    public function test_pjl_ntf_001_notifications_are_scoped_to_seller_store(): void
    {
        $store = $this->makeBlackBoxStore();
        $customer = $this->makeBlackBoxUser();
        $own = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($store), 'processing', ['order_code' => 'ORD-MILIK']);
        $other = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct(), 'processing', ['order_code' => 'ORD-LAIN']);

        Livewire::actingAs($store->user)->test(NotificationBell::class)
            ->assertSee($own->order_code)->assertDontSee($other->order_code)->assertViewHas('unreadCount', 1);
    }

    #[TestDox('PJL-NTF-002 Membuka lonceng menandai notifikasi lama telah dibaca')]
    public function test_pjl_ntf_002_opening_bell_marks_existing_orders_as_read(): void
    {
        Cache::flush();
        $store = $this->makeBlackBoxStore();
        $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct($store), 'processing');

        Livewire::actingAs($store->user)->test(NotificationBell::class)
            ->assertViewHas('unreadCount', 1)->call('toggle')->assertSet('open', true)
            ->assertViewHas('unreadCount', 0)->call('close')->assertSet('open', false);
    }

    #[TestDox('PJL-PRF-001 Form profil memuat data toko seller aktif')]
    public function test_pjl_prf_001_profile_loads_current_store(): void
    {
        $store = $this->makeBlackBoxStore('approved', ['name' => 'Toko Profil', 'description' => 'Deskripsi awal']);
        Livewire::actingAs($store->user)->test(StoreProfile::class)
            ->assertSet('store.id', $store->id)->assertSet('name', 'Toko Profil')->assertSet('description', 'Deskripsi awal');
    }

    #[TestDox('PJL-PRF-002 Nama toko kosong dan kurang dari tiga karakter ditolak')]
    public function test_pjl_prf_002_store_name_boundaries_are_validated(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(StoreProfile::class)->set('name', '')->call('save')->assertHasErrors(['name' => 'required']);
        Livewire::actingAs($store->user)->test(StoreProfile::class)->set('name', 'AB')->call('save')->assertHasErrors(['name' => 'min']);
    }

    #[TestDox('PJL-PRF-003 Deskripsi dan alamat toko mematuhi batas maksimum')]
    public function test_pjl_prf_003_profile_text_maximums_are_validated(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(StoreProfile::class)
            ->set('description', str_repeat('D', 1001))->set('address', str_repeat('A', 501))
            ->call('save')->assertHasErrors(['description' => 'max', 'address' => 'max']);
    }

    #[TestDox('PJL-PRF-004 Perubahan nama memperbarui profil dan slug toko')]
    public function test_pjl_prf_004_valid_profile_update_changes_slug(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(StoreProfile::class)
            ->set('name', 'Nama Toko Baru')->set('description', 'Deskripsi toko baru')
            ->set('address', 'Alamat operasional baru')->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas('stores', ['id' => $store->id, 'name' => 'Nama Toko Baru', 'slug' => 'nama-toko-baru']);
    }

    #[TestDox('PJL-PRF-005 Deskripsi profil membersihkan elemen script')]
    public function test_pjl_prf_005_profile_description_is_sanitized(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(StoreProfile::class)
            ->set('description', '<p>Aman</p><script>alert(1)</script>')->call('save')->assertHasNoErrors();

        $description = $store->fresh()->description;
        $this->assertStringNotContainsString('<script>', $description);
        $this->assertStringContainsString('<p>Aman</p>', $description);
    }

    #[TestDox('PJL-PRF-006 File non-gambar ditolak sebagai logo dan banner')]
    public function test_pjl_prf_006_non_image_profile_files_are_rejected(): void
    {
        Storage::fake('public');
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(StoreProfile::class)
            ->set('newLogo', UploadedFile::fake()->create('logo.jpg', 100, 'application/pdf'))
            ->set('newBanner', UploadedFile::fake()->create('banner.jpg', 100, 'application/pdf'))
            ->call('save')->assertHasErrors(['newLogo' => 'image', 'newBanner' => 'image']);
    }

    #[TestDox('PJL-PRF-007 Dimensi minimum logo dan banner divalidasi')]
    public function test_pjl_prf_007_profile_image_dimensions_are_validated(): void
    {
        Storage::fake('public');
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(StoreProfile::class)
            ->set('newLogo', UploadedFile::fake()->image('logo.jpg', 50, 50))
            ->set('newBanner', UploadedFile::fake()->image('banner.jpg', 200, 100))
            ->call('save')->assertHasErrors(['newLogo' => 'dimensions', 'newBanner' => 'dimensions']);
    }

    #[TestDox('PJL-PRF-008 Logo dan banner valid disimpan pada toko aktif')]
    public function test_pjl_prf_008_valid_logo_and_banner_are_saved(): void
    {
        Storage::fake('public');
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(StoreProfile::class)
            ->set('newLogo', UploadedFile::fake()->image('logo.png', 300, 300))
            ->set('newBanner', UploadedFile::fake()->image('banner.png', 1000, 400))
            ->call('save')->assertHasNoErrors();

        $store->refresh();
        $this->assertNotNull($store->logo);
        $this->assertNotNull($store->banner);
        Storage::disk('public')->assertExists($store->logo);
        Storage::disk('public')->assertExists($store->banner);
    }
}
