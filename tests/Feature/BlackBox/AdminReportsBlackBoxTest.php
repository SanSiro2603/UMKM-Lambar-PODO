<?php

namespace Tests\Feature\BlackBox;

use App\Livewire\Admin\Reports;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class AdminReportsBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    #[TestDox('ADM-RPT-001 Total seller aktif tidak dipengaruhi filter periode')]
    public function test_adm_rpt_001_total_active_sellers_is_global(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $old = $this->makeBlackBoxStore('approved');
        $old->forceFill(['created_at' => now()->subYear()])->save();
        $this->makeBlackBoxStore('approved');
        $this->makeBlackBoxStore('pending');

        Livewire::actingAs($admin)->test(Reports::class)
            ->set('startDate', now()->subDays(7)->format('Y-m-d'))->set('endDate', now()->format('Y-m-d'))
            ->assertViewHas('totalSellers', 2)->assertViewHas('newSellersCount', 1);
    }

    #[TestDox('ADM-RPT-002 Laporan transaksi hanya menghitung processing shipped dan delivered')]
    public function test_adm_rpt_002_report_uses_sales_flow_statuses(): void
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

        Livewire::actingAs($admin)->test(Reports::class)
            ->assertViewHas('transactionsCount', 3)->assertViewHas('totalRevenue', 30000);
    }

    #[TestDox('ADM-RPT-003 Filter tanggal membatasi seller baru dan transaksi')]
    public function test_adm_rpt_003_date_range_filters_sellers_and_orders(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $insideStore = $this->makeBlackBoxStore('approved', ['name' => 'Seller Dalam Periode']);
        $insideStore->forceFill(['created_at' => now()->subDays(2)])->save();
        $outsideStore = $this->makeBlackBoxStore('approved', ['name' => 'Seller Luar Periode']);
        $outsideStore->forceFill(['created_at' => now()->subDays(20)])->save();
        $customer = $this->makeBlackBoxUser();
        $insideOrder = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($insideStore), 'delivered', ['total_price' => 25000]);
        $insideOrder->forceFill(['created_at' => now()->subDays(2)])->save();
        $outsideOrder = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($outsideStore), 'delivered', ['total_price' => 75000]);
        $outsideOrder->forceFill(['created_at' => now()->subDays(20)])->save();

        Livewire::actingAs($admin)->test(Reports::class)
            ->set('startDate', now()->subDays(5)->format('Y-m-d'))->set('endDate', now()->format('Y-m-d'))
            ->assertViewHas('newSellersCount', 1)->assertViewHas('transactionsCount', 1)->assertViewHas('totalRevenue', 25000);
    }

    #[TestDox('ADM-RPT-004 Filter hanya tanggal awal mencakup data setelah tanggal tersebut')]
    public function test_adm_rpt_004_start_date_only_filters_lower_bound(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $recent = $this->makeBlackBoxOrder($customer, $product, 'processing', ['total_price' => 10000]);
        $old = $this->makeBlackBoxOrder($customer, $product, 'processing', ['total_price' => 50000]);
        $old->forceFill(['created_at' => now()->subMonth()])->save();

        Livewire::actingAs($admin)->test(Reports::class)
            ->set('startDate', now()->subDays(7)->format('Y-m-d'))
            ->assertViewHas('transactionsCount', 1)->assertViewHas('totalRevenue', 10000);
    }

    #[TestDox('ADM-RPT-005 Filter hanya tanggal akhir mencakup data sebelum tanggal tersebut')]
    public function test_adm_rpt_005_end_date_only_filters_upper_bound(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct();
        $old = $this->makeBlackBoxOrder($customer, $product, 'processing', ['total_price' => 10000]);
        $old->forceFill(['created_at' => now()->subMonth()])->save();
        $this->makeBlackBoxOrder($customer, $product, 'processing', ['total_price' => 50000]);

        Livewire::actingAs($admin)->test(Reports::class)
            ->set('endDate', now()->subDays(7)->format('Y-m-d'))
            ->assertViewHas('transactionsCount', 1)->assertViewHas('totalRevenue', 10000);
    }

    #[TestDox('ADM-RPT-006 Tanggal akhir sebelum awal ditolak')]
    public function test_adm_rpt_006_end_before_start_is_rejected(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Livewire::actingAs($admin)->test(Reports::class)
            ->set('startDate', '2026-07-10')->set('endDate', '2026-07-01')
            ->assertHasErrors(['endDate'])->assertViewHas('transactionsCount', 0)->assertViewHas('newSellersCount', 0);
    }

    #[TestDox('ADM-RPT-007 Format tanggal tidak valid ditolak')]
    public function test_adm_rpt_007_invalid_date_is_rejected(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Livewire::actingAs($admin)->test(Reports::class)->set('startDate', 'bukan-tanggal')
            ->assertHasErrors(['startDate'])->assertViewHas('transactionsCount', 0);
    }

    #[TestDox('ADM-RPT-008 Peringkat penjual diurutkan berdasarkan omzet dan memuat jumlah produk')]
    public function test_adm_rpt_008_top_sellers_are_ranked_by_revenue(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $customer = $this->makeBlackBoxUser();
        $low = $this->makeBlackBoxStore('approved', ['name' => 'Seller Omzet Rendah']);
        $high = $this->makeBlackBoxStore('approved', ['name' => 'Seller Omzet Tinggi']);
        $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($low), 'delivered', ['total_price' => 10000]);
        $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($high), 'delivered', ['total_price' => 100000]);

        Livewire::actingAs($admin)->test(Reports::class)->assertViewHas('topSellers', function ($rows) use ($high): bool {
            return count($rows) === 2 && $rows[0]['name'] === $high->name && $rows[0]['products'] === 1;
        });
    }

    #[TestDox('ADM-RPT-009 Peringkat laporan dibatasi maksimal lima seller')]
    public function test_adm_rpt_009_top_sellers_are_limited_to_five(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $customer = $this->makeBlackBoxUser();
        for ($i = 1; $i <= 6; $i++) {
            $store = $this->makeBlackBoxStore('approved');
            $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($store), 'delivered', ['total_price' => $i * 10000]);
        }
        Livewire::actingAs($admin)->test(Reports::class)->assertViewHas('topSellers', fn ($rows) => count($rows) === 5);
    }

    #[TestDox('ADM-RPT-010 Rentang maksimal 31 hari membentuk grafik harian')]
    public function test_adm_rpt_010_short_range_builds_daily_charts(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->makeBlackBoxStore('approved');
        $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct($store), 'delivered', ['total_price' => 50000]);
        Livewire::actingAs($admin)->test(Reports::class)
            ->set('startDate', now()->subDay()->format('Y-m-d'))->set('endDate', now()->format('Y-m-d'))
            ->assertViewHas('revenueChartTitle', 'Tren Omzet Harian')
            ->assertViewHas('sellerChartTitle', 'Tren Seller Baru Harian')
            ->assertViewHas('revenueChartHasData', true)->assertViewHas('sellerChartHasData', true);
    }

    #[TestDox('ADM-RPT-011 Semua data membentuk grafik bulanan')]
    public function test_adm_rpt_011_unbounded_report_builds_monthly_charts(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        $store = $this->makeBlackBoxStore('approved');
        $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct($store), 'delivered');
        Livewire::actingAs($admin)->test(Reports::class)
            ->assertViewHas('revenueChartTitle', 'Tren Omzet Bulanan')
            ->assertViewHas('sellerChartTitle', 'Tren Seller Baru Bulanan');
    }

    #[TestDox('ADM-RPT-012 Tautan PDF mempertahankan parameter filter tanggal')]
    public function test_adm_rpt_012_pdf_query_preserves_date_filters(): void
    {
        $admin = $this->makeBlackBoxUser('admin');
        Livewire::actingAs($admin)->test(Reports::class)
            ->set('startDate', '2026-07-01')->set('endDate', '2026-07-18')
            ->assertViewHas('pdfQuery', ['start_date' => '2026-07-01', 'end_date' => '2026-07-18']);
    }
}
