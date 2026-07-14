<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sama seperti district_code di users, tapi untuk lokasi toko —
     * dipakai sebagai titik asal saat menghitung zona ongkir.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->char('district_code', 7)->nullable()->after('address');
        });

        $districtsTable = config('laravolt.indonesia.table_prefix') . 'districts';
        if (! Schema::hasTable($districtsTable)) {
            return;
        }

        $districts = DB::table($districtsTable)->where('city_code', '1804')->get(['code', 'name'])
            ->sortByDesc(fn ($d) => strlen($d->name))
            ->values();

        DB::table('stores')
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($stores) use ($districts) {
                foreach ($stores as $store) {
                    $district = $districts->first(fn ($d) => stripos($store->address, $d->name) !== false);

                    if ($district) {
                        DB::table('stores')->where('id', $store->id)->update(['district_code' => $district->code]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('district_code');
        });
    }
};
