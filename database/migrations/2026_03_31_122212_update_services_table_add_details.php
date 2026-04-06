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
        Schema::table('services', function (Blueprint $table) {
            $table->string('slug')->unique()->after('title');
            $table->string('category'); // VAPT, SOC, Cloud, Consultation
            $table->text('full_description')->nullable();
            $table->text('script_code')->nullable(); // للسكربتات البرمجية
            $table->boolean('is_automated')->default(false);
            $table->boolean('is_visible')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
