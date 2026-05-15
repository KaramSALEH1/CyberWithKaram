<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Remove the existing receipt_path column
            $table->dropColumn('receipt_path');

            // Add new text fields
            $table->string('account_name_number')->nullable()->after('amount');
            $table->decimal('transaction_amount', 12, 2)->nullable()->after('account_name_number');
            $table->string('transaction_id_reference')->nullable()->after('transaction_amount');
            $table->text('notes')->nullable()->after('transaction_id_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Re-add the receipt_path column if rolling back
            $table->string('receipt_path')->nullable()->after('amount');

            // Remove the newly added text fields if rolling back
            $table->dropColumn(['account_name_number', 'transaction_amount', 'transaction_id_reference', 'notes']);
        });
    }
};
