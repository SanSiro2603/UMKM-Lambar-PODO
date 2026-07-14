<?php

namespace Tests\Feature;

use App\Services\ShippingZoneCalculator;
use Tests\TestCase;

class ShippingZoneCalculatorTest extends TestCase
{
    public function test_same_district_uses_same_district_shipping_cost(): void
    {
        $shipping = app(ShippingZoneCalculator::class)->calculate('180404', '180404');

        $this->assertSame(5000, $shipping['cost']);
        $this->assertSame('Kecamatan sama', $shipping['label']);
        $this->assertSame(0.0, $shipping['distance_km']);
    }

    public function test_near_district_uses_adjacent_shipping_cost(): void
    {
        $shipping = app(ShippingZoneCalculator::class)->calculate('180404', '180410');

        $this->assertSame(10000, $shipping['cost']);
        $this->assertSame('Kecamatan bersebelahan', $shipping['label']);
        $this->assertNotNull($shipping['distance_km']);
    }

    public function test_missing_district_data_uses_safe_fallback_shipping_cost(): void
    {
        $shipping = app(ShippingZoneCalculator::class)->calculate(null, '180404');

        $this->assertSame(20000, $shipping['cost']);
        $this->assertSame('Perkiraan (data wilayah tidak lengkap)', $shipping['label']);
        $this->assertNull($shipping['distance_km']);
    }
}
