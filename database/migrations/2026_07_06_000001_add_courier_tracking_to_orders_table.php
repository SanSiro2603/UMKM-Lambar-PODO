<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_phone')->nullable()->after('shipping_address');

            $table->string('courier_name')->nullable()->after('status');
            $table->string('courier_phone')->nullable()->after('courier_name');
            $table->string('courier_token', 64)->nullable()->unique()->after('courier_phone');
            $table->decimal('courier_lat', 10, 7)->nullable()->after('courier_token');
            $table->decimal('courier_lng', 10, 7)->nullable()->after('courier_lat');
            $table->timestamp('courier_location_updated_at')->nullable()->after('courier_lng');
            $table->boolean('is_tracking_active')->default(false)->after('courier_location_updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_phone',
                'courier_name',
                'courier_phone',
                'courier_token',
                'courier_lat',
                'courier_lng',
                'courier_location_updated_at',
                'is_tracking_active',
            ]);
        });
    }
};
