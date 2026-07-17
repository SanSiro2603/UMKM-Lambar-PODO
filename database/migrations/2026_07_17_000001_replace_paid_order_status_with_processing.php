<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Status order menjelaskan tahap pemenuhan pesanan, sedangkan
     * payment_status menjadi satu-satunya sumber status pembayaran.
     */
    public function up(): void
    {
        $this->setStatusValues(['waiting_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled']);

        DB::table('orders')->where('status', 'paid')->update(['status' => 'processing']);

        $this->setStatusValues(['waiting_payment', 'processing', 'shipped', 'delivered', 'cancelled']);
    }

    public function down(): void
    {
        $this->setStatusValues(['waiting_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled']);

        DB::table('orders')->where('status', 'processing')->update(['status' => 'paid']);

        $this->setStatusValues(['waiting_payment', 'paid', 'shipped', 'delivered', 'cancelled']);
    }

    private function setStatusValues(array $values): void
    {
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $enumValues = collect($values)
                ->map(fn (string $value) => DB::getPdo()->quote($value))
                ->implode(',');

            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM({$enumValues}) DEFAULT 'waiting_payment'");
            return;
        }

        Schema::table('orders', function (Blueprint $table) use ($values) {
            $table->enum('status', $values)->default('waiting_payment')->change();
        });
    }
};
