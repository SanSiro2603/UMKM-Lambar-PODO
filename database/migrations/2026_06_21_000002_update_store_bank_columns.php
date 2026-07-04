<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Standardisasi kolom bank di tabel stores untuk sistem Xendit.
     * Stores sudah punya: bank_name, bank_account_number, bank_account_name,
     * bank_code, bank_verification_status, bank_verification_note.
     *
     * Disesuaikan menjadi: bank_code, bank_account_no, bank_account_name,
     * bank_verify_status, bank_reject_reason.
     */
    public function up(): void
    {
        // Step 1: Rename existing columns
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'bank_account_number')) {
                $table->renameColumn('bank_account_number', 'bank_account_no');
            }
            if (Schema::hasColumn('stores', 'bank_verification_status')) {
                $table->renameColumn('bank_verification_status', 'bank_verify_status');
            }
            if (Schema::hasColumn('stores', 'bank_verification_note')) {
                $table->renameColumn('bank_verification_note', 'bank_reject_reason');
            }
        });

        // Step 2: Add missing columns (separate call after rename reflection)
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'bank_code')) {
                $table->string('bank_code')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('stores', 'bank_account_no') && ! Schema::hasColumn('stores', 'bank_account_number')) {
                $table->string('bank_account_no')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('stores', 'bank_verify_status') && ! Schema::hasColumn('stores', 'bank_verification_status')) {
                $table->enum('bank_verify_status', ['unverified', 'pending', 'verified', 'rejected'])->default('unverified')->after('bank_account_name');
            }
            if (! Schema::hasColumn('stores', 'bank_reject_reason') && ! Schema::hasColumn('stores', 'bank_verification_note')) {
                $table->text('bank_reject_reason')->nullable()->after('bank_verify_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'bank_account_no')) {
                $table->renameColumn('bank_account_no', 'bank_account_number');
            }
            if (Schema::hasColumn('stores', 'bank_verify_status')) {
                $table->renameColumn('bank_verify_status', 'bank_verification_status');
            }
            if (Schema::hasColumn('stores', 'bank_reject_reason')) {
                $table->renameColumn('bank_reject_reason', 'bank_verification_note');
            }
        });
    }
};