<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\ShippingZoneCalculator;

return new class extends Migration
{
    /**
     * Ongkir sekarang dihitung otomatis saat checkout (bukan lagi diisi manual
     * oleh seller), jadi order tidak akan pernah lagi berhenti di status
     * 'waiting_shipping_cost'. Tambah kolom label zona untuk riwayat, lalu
     * selesaikan order lama yang masih nyangkut di status itu.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_zone_label')->nullable()->after('shipping_cost');
        });

        $calculator = new ShippingZoneCalculator();

        $stuckOrders = DB::table('orders')
            ->where('status', 'waiting_shipping_cost')
            ->get(['id', 'store_id', 'customer_id', 'total_price', 'payment_method']);

        foreach ($stuckOrders as $order) {
            $store = DB::table('stores')->where('id', $order->store_id)->first(['district_code']);
            $customer = DB::table('users')->where('id', $order->customer_id)->first(['district_code']);

            $shipping = $calculator->calculate($store->district_code ?? null, $customer->district_code ?? null);

            DB::table('orders')->where('id', $order->id)->update([
                'shipping_cost' => $shipping['cost'],
                'shipping_zone_label' => $shipping['label'],
                'total_price' => $order->total_price + $shipping['cost'],
                'status' => $order->payment_method === 'cod' ? 'paid' : 'waiting_payment',
            ]);
        }

        if ($this->canModifyEnumColumns()) {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('waiting_payment','paid','shipped','delivered','cancelled') DEFAULT 'waiting_payment'");
        }
    }

    public function down(): void
    {
        if ($this->canModifyEnumColumns()) {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('waiting_shipping_cost','waiting_payment','paid','shipped','delivered','cancelled') DEFAULT 'waiting_shipping_cost'");
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_zone_label');
        });
    }

    private function canModifyEnumColumns(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};
