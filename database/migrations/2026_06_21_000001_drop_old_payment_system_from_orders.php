<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * HAPUS sistem pembayaran lama (upload bukti transfer + verifikasi manual).
     * Drop kolom proof_of_transfer, ubah enum status ke sistem baru Xendit.
     */
    public function up(): void
    {
        // 1. Hapus kolom bukti transfer (tidak diperlukan lagi)
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_verified_by')) {
                $table->dropForeign(['payment_verified_by']);
            }
        });

        $columnsToDrop = [];
        if (Schema::hasColumn('orders', 'proof_of_transfer')) {
            $columnsToDrop[] = 'proof_of_transfer';
        }
        if (Schema::hasColumn('orders', 'payment_verified_at')) {
            $columnsToDrop[] = 'payment_verified_at';
        }
        if (Schema::hasColumn('orders', 'payment_verified_by')) {
            $columnsToDrop[] = 'payment_verified_by';
        }
        if (Schema::hasColumn('orders', 'payment_rejection_reason')) {
            $columnsToDrop[] = 'payment_rejection_reason';
        }

        if (!empty($columnsToDrop)) {
            Schema::table('orders', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }

        // 2. UPDATE data lama ke nilai baru SEBELUM alter enum
        // payment_method: 'transfer' → 'xendit'
        DB::statement("UPDATE orders SET payment_method = 'xendit' WHERE payment_method = 'transfer'");
        // payment_status: 'verified' → 'paid'
        DB::statement("UPDATE orders SET payment_status = 'paid' WHERE payment_status = 'verified'");
        // status: mapping lama → baru
        DB::statement("UPDATE orders SET status = 'waiting_shipping_cost' WHERE status = 'pending'");
        DB::statement("UPDATE orders SET status = 'waiting_payment' WHERE status = 'menunggu_validasi'");
        DB::statement("UPDATE orders SET status = 'shipped' WHERE status = 'dikirim'");
        DB::statement("UPDATE orders SET status = 'delivered' WHERE status = 'selesai'");
        DB::statement("UPDATE orders SET status = 'cancelled' WHERE status = 'dibatalkan'");
        DB::statement("UPDATE orders SET status = 'paid' WHERE status = 'diproses'");

        // 3. Ubah enum payment_status: hapus 'verified', ganti ke sistem baru
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('unpaid','paid','failed') DEFAULT 'unpaid'");

        // 4. Ubah enum payment_method: hanya 'xendit' dan 'cod'
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('xendit','cod') DEFAULT 'xendit'");

        // 5. Ubah enum status order: alur baru
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('waiting_shipping_cost','waiting_payment','paid','shipped','delivered','cancelled') DEFAULT 'waiting_shipping_cost'");

        // 6. Tambah kolom baru untuk Xendit invoice tracking
        Schema::table('orders', function (Blueprint $table) {
            $table->string('xendit_invoice_id')->unique()->nullable()->after('payment_method');
            $table->text('xendit_invoice_url')->nullable()->after('xendit_invoice_id');
            $table->timestamp('paid_at')->nullable()->after('xendit_invoice_url');
        });
    }

    public function down(): void
    {
        // Drop kolom Xendit
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['xendit_invoice_id', 'xendit_invoice_url', 'paid_at']);
        });

        // Tambah kembali kolom sistem lama
        Schema::table('orders', function (Blueprint $table) {
            $table->string('proof_of_transfer')->nullable()->after('status');
            $table->timestamp('payment_verified_at')->nullable()->after('proof_of_transfer');
            $table->foreignId('payment_verified_by')->nullable()->constrained('users')->onDelete('set null')->after('payment_verified_at');
            $table->text('payment_rejection_reason')->nullable()->after('payment_verified_by');
        });

        // Kembalikan enum ke versi lama (perlu tambah dulu semua nilai baru)
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('xendit','cod','transfer') DEFAULT 'xendit'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('unpaid','paid','failed','verified') DEFAULT 'unpaid'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('waiting_shipping_cost','waiting_payment','paid','shipped','delivered','cancelled','pending','menunggu_validasi','diproses','dikirim','selesai','dibatalkan') DEFAULT 'pending'");

        // UPDATE data kembali ke nilai lama
        DB::statement("UPDATE orders SET payment_method = 'transfer' WHERE payment_method = 'xendit'");
        DB::statement("UPDATE orders SET payment_status = 'verified' WHERE payment_status = 'paid' AND id IN (SELECT id FROM orders WHERE proof_of_transfer IS NOT NULL)");
        DB::statement("UPDATE orders SET status = 'pending' WHERE status = 'waiting_shipping_cost'");
        DB::statement("UPDATE orders SET status = 'menunggu_validasi' WHERE status = 'waiting_payment'");
        DB::statement("UPDATE orders SET status = 'diproses' WHERE status = 'paid'");
        DB::statement("UPDATE orders SET status = 'dikirim' WHERE status = 'shipped'");
        DB::statement("UPDATE orders SET status = 'selesai' WHERE status = 'delivered'");
        DB::statement("UPDATE orders SET status = 'dibatalkan' WHERE status = 'cancelled'");

        // Hapus nilai-nilai baru dari enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('transfer','cod') DEFAULT 'transfer'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('unpaid','paid','verified','failed') DEFAULT 'unpaid'");
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','menunggu_validasi','diproses','dikirim','selesai','dibatalkan') DEFAULT 'pending'");
    }
};