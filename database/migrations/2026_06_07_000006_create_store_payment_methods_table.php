<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'bank', 'gopay', 'ovo', 'dana', 'other'
            $table->string('name'); // e.g. 'Bank Mandiri', 'GoPay', 'OVO'
            $table->string('account_name');
            $table->string('account_number');
            $table->string('qr_code')->nullable();
            $table->timestamps();
        });

        // Migrate existing store bank details to store_payment_methods
        try {
            $stores = DB::table('stores')->get();
            foreach ($stores as $store) {
                if (!empty($store->bank_name) && !empty($store->bank_account_number) && !empty($store->bank_account_name)) {
                    DB::table('store_payment_methods')->insert([
                        'store_id' => $store->id,
                        'type' => 'bank',
                        'name' => $store->bank_name,
                        'account_name' => $store->bank_account_name,
                        'account_number' => $store->bank_account_number,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silence if table or columns don't exist yet in some environments
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_payment_methods');
    }
};
