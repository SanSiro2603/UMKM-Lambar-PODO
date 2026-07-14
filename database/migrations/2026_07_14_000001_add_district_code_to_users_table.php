<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah district_code (kode kecamatan Laravolt Indonesia) ke users,
     * lalu backfill dari kolom address teks lama ("...Kec. X, Kabupaten Lampung Barat")
     * supaya ongkir otomatis bisa dihitung tanpa parsing teks di runtime.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('district_code', 7)->nullable()->after('address');
        });

        $districtsTable = config('laravolt.indonesia.table_prefix') . 'districts';
        if (! Schema::hasTable($districtsTable)) {
            return;
        }

        // Nama kecamatan terpanjang dicek lebih dulu supaya "Bandar Negeri Suoh" tidak
        // salah cocok dengan "Suoh" saja.
        $districts = DB::table($districtsTable)->where('city_code', '1804')->get(['code', 'name'])
            ->sortByDesc(fn ($d) => strlen($d->name))
            ->values();

        DB::table('users')
            ->whereNotNull('address')
            ->where('address', '!=', '')
            ->orderBy('id')
            ->chunkById(200, function ($users) use ($districts) {
                foreach ($users as $user) {
                    $district = $districts->first(fn ($d) => stripos($user->address, $d->name) !== false);

                    if ($district) {
                        DB::table('users')->where('id', $user->id)->update(['district_code' => $district->code]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('district_code');
        });
    }
};
