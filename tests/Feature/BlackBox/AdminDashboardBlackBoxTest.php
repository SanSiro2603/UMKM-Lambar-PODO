<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\Admin\Dashboard;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class AdminDashboardBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    #[TestDox('ADM-ACC-001 Admin dapat membuka seluruh halaman admin')]
    public function test_adm_acc_001_admin_can_open_all_admin_pages(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        foreach (['admin.dashboard', 'admin.sellers', 'admin.categories', 'admin.reports', 'admin.bank.index'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    #[TestDox('ADM-ACC-002 Pengunjung diarahkan ke login dari halaman admin')]
    public function test_adm_acc_002_guest_is_redirected_from_admin_pages(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.bank.index'))->assertRedirect(route('login'));
    }

    #[TestDox('ADM-ACC-003 Pelanggan dan seller ditolak dari halaman admin')]
    public function test_adm_acc_003_non_admin_roles_are_forbidden(): void
    {
        foreach (['customer', 'seller'] as $role) {
            $user = $this->makeBlackBoxUser($role);
            $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
            $this->actingAs($user)->get(route('admin.bank.index'))->assertForbidden();
        }
    }

    #[TestDox('ADM-DSH-001 Total seller hanya menghitung toko approved')]
    public function test_adm_dsh_001_total_sellers_counts_only_approved(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $this->makeBlackBoxStore('approved');
        $this->makeBlackBoxStore('approved');
        $this->makeBlackBoxStore('pending');
        $this->makeBlackBoxStore('rejected');

        Livewire::actingAs($admin)->test(Dashboard::class)->assertViewHas('totalSellers', 2);
    }

    #[TestDox('ADM-DSH-002 Total produk menghitung produk pada seluruh toko')]
    public function test_adm_dsh_002_total_products_counts_platform_products(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $this->makeBlackBoxProduct($this->makeBlackBoxStore('approved'));
        $this->makeBlackBoxProduct($this->makeBlackBoxStore('pending'));
        Livewire::actingAs($admin)->test(Dashboard::class)->assertViewHas('totalProducts', 2);
    }

    #[TestDox('ADM-DSH-003 Total transaksi mengecualikan pesanan cancelled')]
    public function test_adm_dsh_003_total_orders_excludes_cancelled(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        foreach (['waiting_payment', 'processing', 'shipped', 'delivered', 'cancelled'] as $status) {
            $this->makeBlackBoxOrder($customer, $product, $status);
        }
        Livewire::actingAs($admin)->test(Dashboard::class)->assertViewHas('totalOrders', 4);
    }

    #[TestDox('ADM-DSH-004 Omzet hanya menghitung processing shipped dan delivered')]
    public function test_adm_dsh_004_omzet_uses_sales_flow_statuses(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        foreach (['processing', 'shipped', 'delivered'] as $status) {
            $this->makeBlackBoxOrder($customer, $product, $status, ['total_price' => 10000]);
        }
        foreach (['waiting_payment', 'cancelled'] as $status) {
            $this->makeBlackBoxOrder($customer, $product, $status, ['total_price' => 50000]);
        }
        Livewire::actingAs($admin)->test(Dashboard::class)->assertViewHas('totalOmzet', 30000);
    }

    #[TestDox('ADM-DSH-005 Pendapatan platform hanya menjumlahkan fee transaksi disbursed')]
    public function test_adm_dsh_005_platform_revenue_uses_disbursed_fees(): void
    {
        [$admin, $order] = $this->adminAndOrder();
        Transaction::create(['order_id' => $order->id, 'seller_id' => $order->store->user_id, 'total_amount' => 100000, 'platform_fee' => 5000, 'seller_amount' => 95000, 'status' => 'disbursed']);
        $otherOrder = $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct(), 'processing');
        Transaction::create(['order_id' => $otherOrder->id, 'seller_id' => $otherOrder->store->user_id, 'total_amount' => 200000, 'platform_fee' => 10000, 'seller_amount' => 190000, 'status' => 'paid']);

        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertViewHas('platformRevenue', 5000)->assertViewHas('revenue', 5000);
    }

    #[TestDox('ADM-DSH-006 Ringkasan pencairan membedakan disbursed dan paid')]
    public function test_adm_dsh_006_disbursement_summary_uses_transaction_status(): void
    {
        [$admin, $order] = $this->adminAndOrder();
        Transaction::create(['order_id' => $order->id, 'seller_id' => $order->store->user_id, 'total_amount' => 100000, 'seller_amount' => 95000, 'status' => 'disbursed']);
        $otherOrder = $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct(), 'processing');
        Transaction::create(['order_id' => $otherOrder->id, 'seller_id' => $otherOrder->store->user_id, 'total_amount' => 200000, 'seller_amount' => 190000, 'status' => 'paid']);

        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertViewHas('totalDisbursed', 95000)->assertViewHas('pendingDisbursement', 190000);
    }

    #[TestDox('ADM-DSH-007 Daftar verifikasi dashboard hanya memuat lima toko pending terbaru')]
    public function test_adm_dsh_007_pending_list_is_limited_to_five_latest(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        for ($i = 1; $i <= 6; $i++) {
            $store = $this->makeBlackBoxStore('pending', ['name' => sprintf('Pending %02d', $i)]);
            $store->forceFill(['created_at' => now()->addMinutes($i)])->save();
        }
        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertViewHas('pendingSellers', fn ($stores) => $stores->count() === 5)
            ->assertSee('Pending 06')->assertDontSee('Pending 01');
    }

    #[TestDox('ADM-DSH-008 Peringkat toko diurutkan berdasarkan omzet tertinggi')]
    public function test_adm_dsh_008_top_stores_are_ranked_by_revenue(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $customer = $this->makeBlackBoxUser();
        $low = $this->makeBlackBoxStore('approved', ['name' => 'Toko Omzet Rendah']);
        $high = $this->makeBlackBoxStore('approved', ['name' => 'Toko Omzet Tinggi']);
        $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($low), 'delivered', ['total_price' => 10000]);
        $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($high), 'delivered', ['total_price' => 100000]);

        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertViewHas('topStores', fn ($rows) => $rows->first()->store_id === $high->id);
    }

    #[TestDox('ADM-DSH-009 Kategori terpopuler dihitung dari kuantitas produk terjual')]
    public function test_adm_dsh_009_top_categories_use_sold_quantity(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct(null, ['name' => 'Produk Kategori Terlaris']);
        $this->makeBlackBoxOrder($customer, $product, 'delivered', [], 4);

        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertViewHas('categoryLabels', [$product->category->name])->assertViewHas('categoryValues', [4]);
    }

    #[TestDox('ADM-DSH-010 Grafik bulanan mengecualikan transaksi cancelled')]
    public function test_adm_dsh_010_monthly_chart_excludes_cancelled(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $this->makeBlackBoxOrder($customer, $product, 'processing');
        $this->makeBlackBoxOrder($customer, $product, 'cancelled');
        $monthIndex = now()->month - 1;

        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertViewHas('chartOrders', fn ($values) => $values[$monthIndex] === 1 && count($values) === 12);
    }

    #[TestDox('ADM-DSH-011 Admin menyetujui toko pending dari dashboard')]
    public function test_adm_dsh_011_admin_approves_pending_store_from_dashboard(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->makeBlackBoxStore('pending');
        $store->user->forceFill(['role' => 'customer'])->save();
        Livewire::actingAs($admin)->test(Dashboard::class)->call('approveSeller', $store->id)->assertSee('berhasil disetujui');

        $this->assertSame('approved', $store->fresh()->status);
        $this->assertSame('seller', $store->user->fresh()->role);
    }

    #[TestDox('ADM-DSH-012 Admin menolak toko pending dari dashboard')]
    public function test_adm_dsh_012_admin_rejects_pending_store_from_dashboard(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->makeBlackBoxStore('pending');
        Livewire::actingAs($admin)->test(Dashboard::class)->call('rejectSeller', $store->id)->assertSee('ditolak');
        $this->assertSame('rejected', $store->fresh()->status);
    }

    #[TestDox('ADM-DSH-013 ID toko tidak valid pada aksi dashboard menghasilkan 404')]
    public function test_adm_dsh_013_invalid_store_action_is_not_found(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        Livewire::actingAs($admin)->test(Dashboard::class)->call('approveSeller', 999999);
    }

    private function adminAndOrder(): array
    {
        $admin = $this->makeBlackBoxUser('admin');
        $order = $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct(), 'processing');
        return [$admin, $order];
    }
}
