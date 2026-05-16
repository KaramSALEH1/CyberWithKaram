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
            // Modify the legacy column to be nullable so course rows can bypass it safely
            if (Schema::hasColumn('payments', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'service_id')) {
                $table->unsignedBigInteger('service_id')->nullable(false)->change();
            }
        });
    }
};
