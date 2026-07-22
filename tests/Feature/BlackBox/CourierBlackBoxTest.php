<?php

namespace Tests\Feature\BlackBox;

use App\Events\CourierLocationUpdated;
use App\Events\OrderStatusUpdated;
use App\Livewire\CourierTracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Support\BlackBoxFixtures;
use Tests\TestCase;

class CourierBlackBoxTest extends TestCase
{
    use BlackBoxFixtures;
    use RefreshDatabase;

    #[TestDox('KUR-001 Tautan kurir valid dapat dibuka tanpa login')]
    public function test_kur_001_valid_tracking_link_is_public(): void
    {
        $order = $this->shippedOrder();
        $this->get(route('courier.tracking', $order->courier_token))
            ->assertOk()->assertSee($order->order_code);
        $this->assertGuest();
    }

    #[TestDox('KUR-002 Token acak yang tidak terdaftar ditolak')]
    public function test_kur_002_unknown_token_is_rejected(): void
    {
        $this->get(route('courier.tracking', 'token-yang-tidak-terdaftar'))
            ->assertOk()->assertSee('Link Tidak Valid')->assertSee('sudah kadaluarsa');
    }

    #[TestDox('KUR-003 URL kurir tanpa token tidak cocok dengan route')]
    public function test_kur_003_missing_token_route_is_not_found(): void
    {
        $this->get('/lacak-kurir')->assertNotFound();
    }

    #[TestDox('KUR-004 Token pada pesanan selain shipped ditolak')]
    public function test_kur_004_token_for_non_shipped_order_is_rejected(): void
    {
        $order = $this->shippedOrder(['status' => 'processing']);
        $this->get(route('courier.tracking', $order->courier_token))
            ->assertOk()->assertSee('Link Tidak Valid');
    }

    #[TestDox('KUR-005 Token lama tidak berlaku setelah seller menggantinya')]
    public function test_kur_005_replaced_token_is_expired(): void
    {
        $order = $this->shippedOrder(['courier_token' => 'token-lama-kurir']);
        $oldToken = $order->courier_token;
        $order->update(['courier_token' => 'token-baru-kurir']);

        $this->get(route('courier.tracking', $oldToken))->assertSee('Link Tidak Valid');
        $this->get(route('courier.tracking', 'token-baru-kurir'))->assertSee($order->order_code);
    }

    #[TestDox('KUR-006 Token yang sudah dihanguskan ditolak')]
    public function test_kur_006_revoked_token_is_rejected(): void
    {
        $order = $this->shippedOrder(['courier_token' => 'token-dihapus']);
        $order->update(['courier_token' => null]);
        $this->get(route('courier.tracking', 'token-dihapus'))->assertSee('Link Tidak Valid');
    }

    #[TestDox('KUR-007 Halaman kurir menampilkan kode nama dan alamat penerima')]
    public function test_kur_007_page_shows_recipient_information(): void
    {
        $order = $this->shippedOrder([
            'order_code' => 'ORD-KURIR-INFO',
            'shipping_address' => 'Jalan Tujuan Kurir Nomor 99',
        ]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->assertSee('ORD-KURIR-INFO')->assertSee($order->customer->name)
            ->assertSee('Jalan Tujuan Kurir Nomor 99');
    }

    #[TestDox('KUR-008 Tombol telepon menggunakan nomor pengiriman pesanan')]
    public function test_kur_008_phone_link_uses_shipping_phone(): void
    {
        $order = $this->shippedOrder(['shipping_phone' => '089999888877']);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->assertSeeHtml('href="tel:089999888877"')->assertSee('089999888877');
    }

    #[TestDox('KUR-009 Nomor pelanggan digunakan jika nomor pengiriman kosong')]
    public function test_kur_009_customer_phone_is_fallback(): void
    {
        $order = $this->shippedOrder(['shipping_phone' => null]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->assertSeeHtml('href="tel:'.$order->customer->phone.'"');
    }

    #[TestDox('KUR-010 Pesanan COD menampilkan nominal uang yang harus ditagih')]
    public function test_kur_010_cod_order_shows_cash_amount(): void
    {
        $order = $this->shippedOrder(['payment_method' => 'cod', 'total_price' => 125000]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->assertSee('PESANAN COD!')->assertSee('Rp 125.000')->assertSee('Barang Sampai & Uang Diterima');
    }

    #[TestDox('KUR-011 Pesanan non-COD tidak menampilkan instruksi penagihan tunai')]
    public function test_kur_011_online_order_hides_cod_instructions(): void
    {
        $order = $this->shippedOrder(['payment_method' => 'xendit', 'payment_status' => 'paid', 'paid_at' => now()]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->assertDontSee('PESANAN COD!')->assertSee('Barang Sampai')->assertDontSee('Uang Diterima');
    }

    #[TestDox('KUR-012 Status pelacakan aktif dimuat saat halaman dibuka ulang')]
    public function test_kur_012_existing_tracking_state_is_loaded(): void
    {
        $order = $this->shippedOrder(['is_tracking_active' => true]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->assertSet('invalid', false)->assertSet('trackingActive', true);
    }

    #[TestDox('KUR-013 Mulai antar mengaktifkan pelacakan lokasi')]
    public function test_kur_013_start_delivery_activates_tracking(): void
    {
        $order = $this->shippedOrder(['is_tracking_active' => false]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->call('startDelivery')->assertSet('trackingActive', true);
        $this->assertTrue($order->fresh()->is_tracking_active);
    }

    #[TestDox('KUR-014 Mulai antar pada token tidak valid tidak mengubah pesanan')]
    public function test_kur_014_invalid_link_cannot_start_delivery(): void
    {
        Livewire::test(CourierTracking::class, ['token' => 'token-tidak-valid'])
            ->assertSet('invalid', true)->call('startDelivery')->assertSet('trackingActive', false);
    }

    #[TestDox('KUR-015 Lokasi sebelum pelacakan dimulai diabaikan')]
    public function test_kur_015_location_before_start_is_ignored(): void
    {
        Event::fake([CourierLocationUpdated::class]);
        $order = $this->shippedOrder(['is_tracking_active' => false]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->call('updateLocation', -5.035, 104.075);
        $this->assertNull($order->fresh()->courier_lat);
        Event::assertNotDispatched(CourierLocationUpdated::class);
    }

    #[TestDox('KUR-016 Koordinat valid disimpan dan menyiarkan pembaruan lokasi')]
    public function test_kur_016_valid_location_is_saved_and_broadcast(): void
    {
        Event::fake([CourierLocationUpdated::class]);
        $order = $this->shippedOrder(['is_tracking_active' => true]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->call('updateLocation', -5.0351234, 104.0754321);

        $order->refresh();
        $this->assertEqualsWithDelta(-5.0351234, $order->courier_lat, 0.0000001);
        $this->assertEqualsWithDelta(104.0754321, $order->courier_lng, 0.0000001);
        $this->assertNotNull($order->courier_location_updated_at);
        Event::assertDispatched(CourierLocationUpdated::class, fn ($event) => $event->order->id === $order->id);
    }

    #[TestDox('KUR-017 Koordinat pada batas bumi masih diterima')]
    public function test_kur_017_coordinate_boundaries_are_accepted(): void
    {
        Event::fake([CourierLocationUpdated::class]);
        $order = $this->shippedOrder(['is_tracking_active' => true]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->call('updateLocation', -90, -180);
        $this->assertSame(-90.0, $order->fresh()->courier_lat);
        $this->assertSame(-180.0, $order->fresh()->courier_lng);
    }

    #[TestDox('KUR-018 Latitude di luar minus 90 sampai 90 ditolak')]
    public function test_kur_018_invalid_latitude_is_rejected(): void
    {
        Event::fake([CourierLocationUpdated::class]);
        $order = $this->shippedOrder(['is_tracking_active' => true]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->call('updateLocation', 90.0001, 104.0);
        $this->assertNull($order->fresh()->courier_lat);
        Event::assertNotDispatched(CourierLocationUpdated::class);
    }

    #[TestDox('KUR-019 Longitude di luar minus 180 sampai 180 ditolak')]
    public function test_kur_019_invalid_longitude_is_rejected(): void
    {
        Event::fake([CourierLocationUpdated::class]);
        $order = $this->shippedOrder(['is_tracking_active' => true]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->call('updateLocation', -5.0, 180.0001);
        $this->assertNull($order->fresh()->courier_lng);
        Event::assertNotDispatched(CourierLocationUpdated::class);
    }

    #[TestDox('KUR-020 Pesanan yang berubah dari shipped tidak menerima lokasi baru')]
    public function test_kur_020_non_shipped_order_does_not_accept_location(): void
    {
        Event::fake([CourierLocationUpdated::class]);
        $order = $this->shippedOrder(['is_tracking_active' => true]);
        $token = $order->courier_token;
        $component = Livewire::test(CourierTracking::class, ['token' => $token]);
        $order->update(['status' => 'delivered']);
        $component->call('updateLocation', -5.0, 104.0);
        $this->assertNull($order->fresh()->courier_lat);
        Event::assertNotDispatched(CourierLocationUpdated::class);
    }

    #[TestDox('KUR-021 Pembaruan lokasi berikutnya mengganti koordinat dan waktu terakhir')]
    public function test_kur_021_repeated_location_updates_replace_latest_position(): void
    {
        Event::fake([CourierLocationUpdated::class]);
        $order = $this->shippedOrder(['is_tracking_active' => true]);
        $component = Livewire::test(CourierTracking::class, ['token' => $order->courier_token]);
        $component->call('updateLocation', -5.0, 104.0);
        $firstUpdatedAt = $order->fresh()->courier_location_updated_at;
        $this->travel(31)->seconds();
        $component->call('updateLocation', -5.1, 104.1);

        $order->refresh();
        $this->assertSame(-5.1, $order->courier_lat);
        $this->assertSame(104.1, $order->courier_lng);
        $this->assertTrue($order->courier_location_updated_at->gt($firstUpdatedAt));
        Event::assertDispatchedTimes(CourierLocationUpdated::class, 2);
    }

    #[TestDox('KUR-022 Penyelesaian COD menandai delivered paid dan mematikan token')]
    public function test_kur_022_cod_completion_marks_paid_and_revokes_token(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $order = $this->shippedOrder(['payment_method' => 'cod', 'payment_status' => 'unpaid', 'is_tracking_active' => true]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])
            ->call('completeDelivery')->assertSet('trackingActive', false)->assertSet('justCompleted', true)
            ->assertSee('Pengantaran Selesai');

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertNull($order->courier_token);
        $this->assertFalse($order->is_tracking_active);
        Event::assertDispatched(OrderStatusUpdated::class, fn ($event) => str_contains($event->message, 'pembayaran COD'));
    }

    #[TestDox('KUR-023 Penyelesaian Xendit mempertahankan status dan waktu pembayaran')]
    public function test_kur_023_online_completion_preserves_payment_data(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $paidAt = now()->subHour();
        $order = $this->shippedOrder(['payment_method' => 'xendit', 'payment_status' => 'paid', 'paid_at' => $paidAt]);
        Livewire::test(CourierTracking::class, ['token' => $order->courier_token])->call('completeDelivery');

        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame($paidAt->timestamp, $order->paid_at->timestamp);
    }

    #[TestDox('KUR-024 Token tidak valid tidak dapat menyelesaikan pesanan')]
    public function test_kur_024_invalid_token_cannot_complete_order(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        Livewire::test(CourierTracking::class, ['token' => 'token-invalid'])
            ->call('completeDelivery')->assertSet('justCompleted', false);
        Event::assertNotDispatched(OrderStatusUpdated::class);
    }

    #[TestDox('KUR-025 Penyelesaian kedua tidak mengubah pesanan atau menyiarkan ulang')]
    public function test_kur_025_completion_is_idempotent(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $order = $this->shippedOrder(['payment_method' => 'cod']);
        $component = Livewire::test(CourierTracking::class, ['token' => $order->courier_token]);
        $component->call('completeDelivery')->call('completeDelivery');
        $this->assertSame('delivered', $order->fresh()->status);
        Event::assertDispatchedTimes(OrderStatusUpdated::class, 1);
    }

    #[TestDox('KUR-026 Tautan lama tidak dapat dibuka lagi setelah pengantaran selesai')]
    public function test_kur_026_completed_order_link_becomes_invalid(): void
    {
        Event::fake([OrderStatusUpdated::class]);
        $order = $this->shippedOrder();
        $token = $order->courier_token;
        Livewire::test(CourierTracking::class, ['token' => $token])->call('completeDelivery');
        $this->get(route('courier.tracking', $token))->assertSee('Link Tidak Valid');
    }

    #[TestDox('KUR-027 Payload lokasi memuat order koordinat dan waktu pembaruan')]
    public function test_kur_027_location_event_payload_is_complete(): void
    {
        $order = $this->shippedOrder([
            'courier_lat' => -5.123, 'courier_lng' => 104.456,
            'courier_location_updated_at' => now(),
        ])->fresh();
        $payload = (new CourierLocationUpdated($order))->broadcastWith();
        $this->assertSame($order->id, $payload['order_id']);
        $this->assertSame(-5.123, $payload['lat']);
        $this->assertSame(104.456, $payload['lng']);
        $this->assertNotNull($payload['updated_at']);
    }

    #[TestDox('KUR-028 Payload status selesai memuat pelanggan status dan pesan')]
    public function test_kur_028_status_event_payload_is_complete(): void
    {
        $order = $this->shippedOrder(['status' => 'delivered']);
        $payload = (new OrderStatusUpdated($order, 'Pesanan selesai'))->broadcastWith();
        $this->assertSame($order->id, $payload['order_id']);
        $this->assertSame($order->customer_id, $payload['customer_id']);
        $this->assertSame('delivered', $payload['status']);
        $this->assertSame('Pesanan selesai', $payload['message']);
    }

    private function shippedOrder(array $attributes = []): \App\Models\Order
    {
        return $this->makeBlackBoxOrder(
            $this->makeBlackBoxUser('customer'),
            $this->makeBlackBoxProduct(),
            $attributes['status'] ?? 'shipped',
            array_merge([
                'courier_name' => 'Kurir Pengujian',
                'courier_phone' => '081299988877',
                'courier_token' => 'kurir-'.strtolower(\Illuminate\Support\Str::random(32)),
                'is_tracking_active' => false,
            ], $attributes),
        );
    }
}
