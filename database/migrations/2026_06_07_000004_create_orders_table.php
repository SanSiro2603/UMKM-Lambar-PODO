<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('total_price');
            $table->unsignedInteger('shipping_cost')->nullable();
            $table->text('shipping_address');
            $table->enum('payment_method', ['transfer', 'cod']);
            $table->enum('payment_status', ['unpaid', 'paid', 'verified', 'failed'])->default('unpaid');
            $table->enum('status', ['pending', 'menunggu_validasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan'])->default('pending');
            $table->string('proof_of_transfer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
