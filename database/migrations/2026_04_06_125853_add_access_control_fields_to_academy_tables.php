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
        Schema::table('courses', function (Blueprint $table) {
            $table->boolean('requires_purchase')->default(false)->after('is_active');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('requires_purchase')->default(false)->after('order_no');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->boolean('requires_purchase')->default(false)->after('order_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('requires_purchase');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('requires_purchase');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('requires_purchase');
        });
    }
};
