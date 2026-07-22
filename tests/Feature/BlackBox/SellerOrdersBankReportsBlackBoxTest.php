<?php

namespace Tests\Feature\BlackBox;

use App\Events\OrderStatusUpdated;
use App\Livewire\Seller\BankAccount;
use App\Livewire\Seller\Orders;
use App\Livewire\Seller\Reports;
use App\Models\OrderItem;
use App\Services\XenditService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Mockery;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class SellerOrdersBankReportsBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    #[TestDox('PJL-ORD-001 Daftar pesanan hanya menampilkan pesanan toko seller aktif')]
    public function test_pjl_ord_001_order_list_is_scoped_to_store(): void
    {
        $store = $this->makeBlackBoxStore();
        $customer = $this->makeBlackBoxUser();
        $own = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($store), 'processing', ['order_code' => 'ORD-SELLER-OWN']);
        $other = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct(), 'processing', ['order_code' => 'ORD-SELLER-OTHER']);

        Livewire::actingAs($store->user)->test(Orders::class)
            ->assertSee($own->order_code)->assertDontSee($other->order_code);
    }

    #[TestDox('PJL-ORD-002 Tab status memfilter pesanan sesuai status')]
    public function test_pjl_ord_002_status_tab_filters_orders(): void
    {
        $store = $this->makeBlackBoxStore();
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct($store);
        $processing = $this->makeBlackBoxOrder($customer, $product, 'processing', ['order_code' => 'ORD-PROCESSING']);
        $delivered = $this->makeBlackBoxOrder($customer, $product, 'delivered', ['order_code' => 'ORD-DELIVERED']);

        Livewire::actingAs($store->user)->test(Orders::class)->call('selectTab', 'delivered')
            ->assertSet('statusTab', 'delivered')->assertSee($delivered->order_code)->assertDontSee($processing->order_code);
    }

    #[TestDox('PJL-ORD-003 Pagination daftar pesanan membatasi 20 baris')]
    public function test_pjl_ord_003_orders_are_paginated_by_twenty(): void
    {
        $store = $this->makeBlackBoxStore();
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct($store);
        for ($i = 1; $i <= 21; $i++) {
            $this->makeBlackBoxOrder($customer, $product, 'processing', ['order_code' => sprintf('ORD-PAGE-%02d', $i)]);
        }

        Livewire::actingAs($store->user)->test(Orders::class)
            ->assertViewHas('orders', fn ($orders) => $orders->count() === 20 && $orders->total() === 21);
    }

    #[TestDox('PJL-ORD-004 Seller dapat membuka detail pesanan tokonya')]
    public function test_pjl_ord_004_seller_can_open_owned_order(): void
    {
        $store = $this->makeBlackBoxStore();
        $order = $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct($store), 'processing');
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->assertSet('view', 'show')->assertSet('orderId', $order->id)->assertSee($order->order_code);
    }

    #[TestDox('PJL-ORD-005 Seller tidak dapat membuka pesanan toko lain')]
    public function test_pjl_ord_005_other_store_order_cannot_be_opened(): void
    {
        $store = $this->makeBlackBoxStore();
        $other = $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct(), 'processing');
        $this->actingAs($store->user)->get(route('seller.orders', $other->id))->assertNotFound();
    }

    #[TestDox('PJL-ORD-006 Nama dan nomor kurir wajib diisi')]
    public function test_pjl_ord_006_courier_fields_are_required(): void
    {
        [$store, $order] = $this->sellerOrder('processing');
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->call('sendCourierAccess')->assertHasErrors(['courierName' => 'required', 'courierPhone' => 'required']);
    }

    #[TestDox('PJL-ORD-007 Nomor kurir mematuhi format dan batas panjang')]
    public function test_pjl_ord_007_courier_phone_is_validated(): void
    {
        [$store, $order] = $this->sellerOrder('processing');
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->set('courierName', 'Kurir')->set('courierPhone', 'ABC123')->call('sendCourierAccess')
            ->assertHasErrors(['courierPhone']);
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->set('courierName', 'Kurir')->set('courierPhone', '0812345678901234')->call('sendCourierAccess')
            ->assertHasErrors(['courierPhone' => 'max']);
    }

    #[TestDox('PJL-ORD-008 Hanya pesanan processing dapat ditugaskan kepada kurir')]
    public function test_pjl_ord_008_only_processing_order_can_be_sent(): void
    {
        [$store, $order] = $this->sellerOrder('waiting_payment');
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->set('courierName', 'Kurir Podo')->set('courierPhone', '081234567899')
            ->call('sendCourierAccess')->assertSee('Pesanan belum siap diproses untuk pengiriman.');
        $this->assertSame('waiting_payment', $order->fresh()->status);
        $this->assertNull($order->fresh()->courier_token);
    }

    #[TestDox('PJL-ORD-009 Penugasan kurir membuat token dan mengubah status menjadi shipped')]
    public function test_pjl_ord_009_assigning_courier_issues_token_and_ships_order(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        [$store, $order] = $this->sellerOrder('processing');
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->set('courierName', 'Kurir Podo')->set('courierPhone', '081234567899')
            ->call('sendCourierAccess')->assertHasNoErrors()->assertDispatched('open-whatsapp');

        $order->refresh();
        $this->assertSame('shipped', $order->status);
        $this->assertSame('Kurir Podo', $order->courier_name);
        $this->assertSame(40, strlen($order->courier_token));
        $this->assertFalse($order->is_tracking_active);
        Event::assertDispatched(OrderStatusUpdated::class);
    }

    #[TestDox('PJL-ORD-010 Nomor lokal kurir diformat menjadi nomor WhatsApp Indonesia')]
    public function test_pjl_ord_010_whatsapp_url_uses_indonesian_number(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        [$store, $order] = $this->sellerOrder('processing');
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->set('courierName', 'Kurir Podo')->set('courierPhone', '081234567899')
            ->call('sendCourierAccess')
            ->assertDispatched('open-whatsapp', fn ($name, $params) => str_starts_with($params['url'], 'https://wa.me/6281234567899'));
    }

    #[TestDox('PJL-ORD-011 Form edit kurir hanya tersedia pada pesanan shipped bertoken')]
    public function test_pjl_ord_011_edit_courier_requires_shipped_tokenized_order(): void
    {
        [$store, $order] = $this->sellerOrder('processing');
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->call('editCourierAccess')->assertSee('Info kurir tidak dapat diubah untuk pesanan ini.')->assertSet('editingCourier', false);
    }

    #[TestDox('PJL-ORD-012 Edit kurir memuat data kurir yang sedang ditugaskan')]
    public function test_pjl_ord_012_edit_courier_prefills_current_data(): void
    {
        [$store, $order] = $this->sellerOrder('shipped', ['courier_name' => 'Kurir Lama', 'courier_phone' => '081111111111', 'courier_token' => 'token-lama']);
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->call('editCourierAccess')->assertSet('editingCourier', true)
            ->assertSet('courierName', 'Kurir Lama')->assertSet('courierPhone', '081111111111');
    }

    #[TestDox('PJL-ORD-013 Pembaruan kurir mengganti token lama dan mereset pelacakan')]
    public function test_pjl_ord_013_updating_courier_rotates_token(): void
    {
        [$store, $order] = $this->sellerOrder('shipped', [
            'courier_name' => 'Kurir Lama', 'courier_phone' => '081111111111',
            'courier_token' => 'token-lama', 'is_tracking_active' => true,
            'courier_lat' => -5.0, 'courier_lng' => 104.0,
        ]);
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->set('courierName', 'Kurir Baru')->set('courierPhone', '082222222222')
            ->call('updateCourierAccess')->assertHasNoErrors()->assertDispatched('open-whatsapp');

        $order->refresh();
        $this->assertNotSame('token-lama', $order->courier_token);
        $this->assertSame('Kurir Baru', $order->courier_name);
        $this->assertFalse($order->is_tracking_active);
        $this->assertNull($order->courier_lat);
        $this->assertNull($order->courier_lng);
    }

    #[TestDox('PJL-ORD-014 Batal edit kurir membersihkan formulir')]
    public function test_pjl_ord_014_cancel_courier_edit_clears_form(): void
    {
        [$store, $order] = $this->sellerOrder('shipped', ['courier_token' => 'token-lama']);
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->set('courierName', 'Sementara')->set('courierPhone', '081234567890')
            ->set('editingCourier', true)->call('cancelEditCourier')
            ->assertSet('editingCourier', false)->assertSet('courierName', '')->assertSet('courierPhone', '');
    }

    #[TestDox('PJL-ORD-015 Penyelesaian manual COD menandai delivered dan paid')]
    public function test_pjl_ord_015_manual_cod_completion_marks_paid(): void
    {
        [$store, $order] = $this->sellerOrder('shipped', ['courier_token' => 'token-aktif', 'is_tracking_active' => true]);
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])->call('completeOrder');

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertNull($order->courier_token);
        $this->assertFalse($order->is_tracking_active);
    }

    #[TestDox('PJL-ORD-016 Penyelesaian manual Xendit mempertahankan status pembayaran')]
    public function test_pjl_ord_016_manual_xendit_completion_preserves_payment_state(): void
    {
        [$store, $order] = $this->sellerOrder('shipped', ['payment_method' => 'xendit', 'payment_status' => 'paid', 'paid_at' => now()]);
        $paidAt = $order->paid_at;
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])->call('completeOrder');

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertTrue($paidAt->equalTo($order->paid_at));
    }

    #[TestDox('PJL-ORD-017 Pesanan yang belum shipped tidak dapat diselesaikan manual')]
    public function test_pjl_ord_017_non_shipped_order_cannot_be_completed(): void
    {
        [$store, $order] = $this->sellerOrder('processing');
        Livewire::actingAs($store->user)->test(Orders::class, ['id' => $order->id])
            ->call('completeOrder')->assertSee('Pesanan belum dikirim.');
        $this->assertSame('processing', $order->fresh()->status);
    }

    #[TestDox('PJL-ORD-018 Seller tidak dapat menugaskan kurir pada pesanan toko lain')]
    public function test_pjl_ord_018_other_store_order_cannot_be_mutated(): void
    {
        $store = $this->makeBlackBoxStore();
        $other = $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct(), 'processing');
        $this->expectException(ModelNotFoundException::class);
        Livewire::actingAs($store->user)->test(Orders::class)
            ->set('orderId', $other->id)->set('courierName', 'Kurir')->set('courierPhone', '081234567890')
            ->call('sendCourierAccess');
    }

    #[TestDox('PJL-BNK-001 Form rekening memuat data rekening toko')]
    public function test_pjl_bnk_001_bank_form_loads_store_data(): void
    {
        $store = $this->makeBlackBoxStore('approved', ['bank_code' => 'BRI', 'bank_account_no' => '1234567890', 'bank_account_name' => 'PEMILIK TOKO']);
        Livewire::actingAs($store->user)->test(BankAccount::class)
            ->assertSet('bank_code', 'BRI')->assertSet('bank_account_no', '1234567890')->assertSet('bank_account_name', 'PEMILIK TOKO');
    }

    #[TestDox('PJL-BNK-002 Seluruh field rekening wajib diisi')]
    public function test_pjl_bnk_002_bank_fields_are_required(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(BankAccount::class)
            ->set('bank_code', '')->set('bank_account_no', '')->set('bank_account_name', '')
            ->call('save')->assertHasErrors(['bank_code' => 'required', 'bank_account_no' => 'required', 'bank_account_name' => 'required']);
    }

    #[TestDox('PJL-BNK-003 Nomor rekening hanya menerima angka')]
    public function test_pjl_bnk_003_account_number_must_be_numeric(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(BankAccount::class)
            ->set('bank_code', 'BRI')->set('bank_account_no', '123ABC')->set('bank_account_name', 'PEMILIK TOKO')
            ->call('save')->assertHasErrors(['bank_account_no' => 'regex']);
    }

    #[TestDox('PJL-BNK-004 Nama pemilik rekening hanya menerima huruf spasi dan titik')]
    public function test_pjl_bnk_004_account_holder_name_is_validated(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(BankAccount::class)
            ->set('bank_code', 'BRI')->set('bank_account_no', '1234567890')->set('bank_account_name', 'PEMILIK 123')
            ->call('save')->assertHasErrors(['bank_account_name' => 'regex']);
    }

    #[TestDox('PJL-BNK-005 Rekening yang gagal validasi sandbox tidak disimpan')]
    public function test_pjl_bnk_005_failed_bank_validation_does_not_update_store(): void
    {
        $store = $this->makeBlackBoxStore('approved', ['bank_code' => 'BRI', 'bank_account_no' => '111', 'bank_account_name' => 'NAMA LAMA']);
        $this->mockBankValidation(['success' => false, 'message' => 'Rekening tidak valid']);

        Livewire::actingAs($store->user)->test(BankAccount::class)
            ->set('bank_code', 'BNI')->set('bank_account_no', '222')->set('bank_account_name', 'NAMA BARU')
            ->call('save')->assertSee('Rekening tidak valid');
        $this->assertDatabaseHas('stores', ['id' => $store->id, 'bank_code' => 'BRI', 'bank_account_no' => '111']);
    }

    #[TestDox('PJL-BNK-006 Rekening valid disimpan kembali sebagai pending verifikasi')]
    public function test_pjl_bnk_006_valid_bank_update_resets_verification(): void
    {
        $store = $this->makeBlackBoxStore('approved', ['bank_verify_status' => 'rejected', 'bank_reject_reason' => 'Data tidak cocok']);
        $this->mockBankValidation(['success' => true, 'data' => ['bank_code' => 'BNI']]);

        Livewire::actingAs($store->user)->test(BankAccount::class)
            ->set('bank_code', 'BNI')->set('bank_account_no', '2222222222')->set('bank_account_name', 'NAMA BARU')
            ->call('save')->assertHasNoErrors()->assertSee('Rekening berhasil didaftarkan. Menunggu verifikasi admin.');
        $this->assertDatabaseHas('stores', [
            'id' => $store->id, 'bank_code' => 'BNI', 'bank_account_no' => '2222222222',
            'bank_verify_status' => 'pending', 'bank_reject_reason' => null,
        ]);
    }

    #[TestDox('PJL-RPT-001 Laporan hanya memuat penjualan toko seller aktif')]
    public function test_pjl_rpt_001_report_is_scoped_to_store(): void
    {
        $store = $this->makeBlackBoxStore();
        $customer = $this->makeBlackBoxUser();
        $own = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct($store), 'delivered', ['order_code' => 'ORD-RPT-OWN', 'total_price' => 100000]);
        $other = $this->makeBlackBoxOrder($customer, $this->makeBlackBoxProduct(), 'delivered', ['order_code' => 'ORD-RPT-OTHER', 'total_price' => 900000]);

        Livewire::actingAs($store->user)->test(Reports::class)
            ->assertSee($own->order_code)->assertDontSee($other->order_code)->assertViewHas('totalSales', 100000);
    }

    #[TestDox('PJL-RPT-002 Laporan hanya menghitung processing shipped dan delivered')]
    public function test_pjl_rpt_002_report_includes_only_sales_flow_statuses(): void
    {
        $store = $this->makeBlackBoxStore();
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct($store);
        foreach (['processing', 'shipped', 'delivered'] as $status) {
            $this->makeBlackBoxOrder($customer, $product, $status, ['total_price' => 10000]);
        }
        foreach (['waiting_payment', 'cancelled'] as $status) {
            $this->makeBlackBoxOrder($customer, $product, $status, ['total_price' => 50000]);
        }

        Livewire::actingAs($store->user)->test(Reports::class)
            ->assertViewHas('totalSales', 30000)->assertViewHas('completedOrdersCount', 3)->assertViewHas('averageOrderValue', 10000);
    }

    #[TestDox('PJL-RPT-003 Filter tanggal awal dan akhir membatasi data laporan')]
    public function test_pjl_rpt_003_date_range_filters_sales(): void
    {
        $store = $this->makeBlackBoxStore();
        $customer = $this->makeBlackBoxUser();
        $product = $this->makeBlackBoxProduct($store);
        $inside = $this->makeBlackBoxOrder($customer, $product, 'delivered', ['order_code' => 'ORD-DALAM', 'total_price' => 25000]);
        $inside->forceFill(['created_at' => now()->subDays(2)])->save();
        $outside = $this->makeBlackBoxOrder($customer, $product, 'delivered', ['order_code' => 'ORD-LUAR', 'total_price' => 75000]);
        $outside->forceFill(['created_at' => now()->subDays(20)])->save();

        Livewire::actingAs($store->user)->test(Reports::class)
            ->set('startDate', now()->subDays(5)->format('Y-m-d'))->set('endDate', now()->format('Y-m-d'))
            ->assertSee('ORD-DALAM')->assertDontSee('ORD-LUAR')->assertViewHas('totalSales', 25000);
    }

    #[TestDox('PJL-RPT-004 Tanggal akhir sebelum tanggal awal ditolak')]
    public function test_pjl_rpt_004_end_date_before_start_is_rejected(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(Reports::class)
            ->set('startDate', '2026-07-10')->set('endDate', '2026-07-01')
            ->assertHasErrors(['endDate'])->assertViewHas('sales', fn ($sales) => $sales->isEmpty());
    }

    #[TestDox('PJL-RPT-005 Format tanggal tidak valid ditolak')]
    public function test_pjl_rpt_005_invalid_date_format_is_rejected(): void
    {
        $store = $this->makeBlackBoxStore();
        Livewire::actingAs($store->user)->test(Reports::class)
            ->set('startDate', 'bukan-tanggal')->assertHasErrors(['startDate'])
            ->assertViewHas('sales', fn ($sales) => $sales->isEmpty());
    }

    #[TestDox('PJL-RPT-006 Produk terlaris dihitung dari kuantitas item penjualan')]
    public function test_pjl_rpt_006_best_seller_uses_item_quantity(): void
    {
        $store = $this->makeBlackBoxStore();
        $customer = $this->makeBlackBoxUser();
        $winner = $this->makeBlackBoxProduct($store, ['name' => 'Produk Terlaris']);
        $other = $this->makeBlackBoxProduct($store, ['name' => 'Produk Biasa']);
        $winnerOrder = $this->makeBlackBoxOrder($customer, $winner, 'delivered', [], 4);
        $otherOrder = $this->makeBlackBoxOrder($customer, $other, 'delivered', [], 1);

        Livewire::actingAs($store->user)->test(Reports::class)->assertViewHas('bestSellerName', 'Produk Terlaris');
    }

    #[TestDox('PJL-RPT-007 Grafik laporan harian dibentuk untuk rentang maksimal 31 hari')]
    public function test_pjl_rpt_007_short_range_builds_daily_chart(): void
    {
        $store = $this->makeBlackBoxStore();
        $order = $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct($store), 'delivered', ['total_price' => 50000]);
        Livewire::actingAs($store->user)->test(Reports::class)
            ->set('startDate', now()->subDay()->format('Y-m-d'))->set('endDate', now()->format('Y-m-d'))
            ->assertViewHas('chartTitle', 'Tren Pendapatan Harian')->assertViewHas('chartHasData', true);
    }

    private function sellerOrder(string $status, array $attributes = []): array
    {
        $store = $this->makeBlackBoxStore();
        $order = $this->makeBlackBoxOrder($this->makeBlackBoxUser(), $this->makeBlackBoxProduct($store), $status, $attributes);
        return [$store, $order];
    }

    private function mockBankValidation(array $result): void
    {
        $xendit = Mockery::mock(XenditService::class);
        $xendit->shouldReceive('validateBankAccount')->once()->andReturn($result);
        $this->app->instance(XenditService::class, $xendit);
    }
}
