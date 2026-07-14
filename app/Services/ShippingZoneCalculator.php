<?php

namespace App\Services;

class ShippingZoneCalculator
{
    /** Titik pusat 15 kecamatan Kabupaten Lampung Barat (code => [lat, lng, name]). */
    private const DISTRICTS = [
        '180404' => [-5.023502, 104.074400, 'Balik Bukit'],
        '180405' => [-4.993379, 104.459704, 'Sumber Jaya'],
        '180406' => [-4.952666, 104.195592, 'Belalau'],
        '180407' => [-5.048606, 104.373070, 'Way Tenong'],
        '180408' => [-5.048944, 104.299066, 'Sekincau'],
        '180409' => [-5.285116, 104.305547, 'Suoh'],
        '180410' => [-5.058566, 104.169505, 'Batu Brak'],
        '180411' => [-4.947289, 104.041992, 'Sukau'],
        '180415' => [-5.100756, 104.481907, 'Gedung Surian'],
        '180418' => [-5.055826, 104.539855, 'Kebun Tebu'],
        '180419' => [-5.124053, 104.404056, 'Air Hitam'],
        '180420' => [-4.928881, 104.355947, 'Pagar Dewa'],
        '180421' => [-5.010495, 104.250077, 'Batu Ketulis'],
        '180422' => [-4.934754, 103.908640, 'Lumbok Seminung'],
        '180423' => [-5.172489, 104.243379, 'Bandar Negeri Suoh'],
    ];

    private const NEAR_KM = 15;
    private const FAR_KM = 30;

    private const COST_SAME = 5000;
    private const COST_NEAR = 10000;
    private const COST_FAR = 15000;
    private const COST_FARTHEST = 20000;

    /**
     * Hitung ongkir berdasarkan zona jarak antara kecamatan toko & kecamatan pembeli.
     *
     * @return array{cost:int,label:string,distance_km:?float}
     */
    public function calculate(?string $originDistrictCode, ?string $destDistrictCode): array
    {
        if (! $originDistrictCode || ! $destDistrictCode) {
            return [
                'cost' => self::COST_FARTHEST,
                'label' => 'Perkiraan (data wilayah tidak lengkap)',
                'distance_km' => null,
            ];
        }

        if ($originDistrictCode === $destDistrictCode) {
            return [
                'cost' => self::COST_SAME,
                'label' => 'Kecamatan sama',
                'distance_km' => 0.0,
            ];
        }

        $origin = self::DISTRICTS[$originDistrictCode] ?? null;
        $dest = self::DISTRICTS[$destDistrictCode] ?? null;

        if (! $origin || ! $dest) {
            return [
                'cost' => self::COST_FARTHEST,
                'label' => 'Perkiraan (data wilayah tidak lengkap)',
                'distance_km' => null,
            ];
        }

        $distanceKm = $this->haversineKm($origin[0], $origin[1], $dest[0], $dest[1]);

        if ($distanceKm <= self::NEAR_KM) {
            return ['cost' => self::COST_NEAR, 'label' => 'Kecamatan bersebelahan', 'distance_km' => $distanceKm];
        }

        if ($distanceKm <= self::FAR_KM) {
            return ['cost' => self::COST_FAR, 'label' => 'Kecamatan agak jauh', 'distance_km' => $distanceKm];
        }

        return ['cost' => self::COST_FARTHEST, 'label' => 'Kecamatan paling jauh', 'distance_km' => $distanceKm];
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($a)));
    }
}
