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
            if (!Schema::hasColumn('courses', 'price')) {
                $table->decimal('price', 8, 2)->default(0.00)->after('slug');
            }
        });

        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'price')) {
                $table->decimal('price', 8, 2)->nullable()->after('title'); // Nullable means it inherits from course unless specified
            }
        });

        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'price')) {
                $table->decimal('price', 8, 2)->nullable()->after('slug'); // Nullable means it inherits from module/course
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('price');
        });
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('price');
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
