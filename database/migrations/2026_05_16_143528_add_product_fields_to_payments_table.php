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
            $table->unsignedBigInteger('product_id')->nullable()->after('service_id');
            $table->string('product_type')->default('service')->after('product_id'); // 'service' or 'course'
            $table->decimal('amount', 15, 2)->change(); // Ensure enough precision
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['product_id', 'product_type']);
        });
    }
};
