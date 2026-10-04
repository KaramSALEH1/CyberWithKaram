<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('lessons', 'module_id')) {
            return;
        }

        Schema::table('lessons', function (Blueprint $table) {
            $table->foreignId('module_id')->constrained()->onDelete('cascade')->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('lessons', 'module_id')) {
            return;
        }

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropForeign(['module_id']);
            $table->dropColumn('module_id');
        });
    }
};
