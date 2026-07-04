<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel transactions untuk tracking invoice Xendit & disbursement.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');

            // Amount
            $table->unsignedInteger('total_amount');
            $table->unsignedInteger('platform_fee')->nullable()->default(0);
            $table->unsignedInteger('seller_amount')->nullable()->default(0);

            // Xendit Invoice
            $table->string('xendit_invoice_id')->unique()->nullable();
            $table->string('xendit_payment_method')->nullable();
            $table->string('xendit_payment_channel')->nullable();
            $table->text('xendit_invoice_url')->nullable();

            // Xendit Disbursement
            $table->string('xendit_disbursement_id')->unique()->nullable();

            // Status
            $table->enum('status', ['pending', 'paid', 'expired', 'disbursed', 'failed'])
                ->default('pending');

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('disbursed_at')->nullable();

            // Extra
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Index
            $table->index(['order_id', 'status']);
            $table->index('xendit_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};