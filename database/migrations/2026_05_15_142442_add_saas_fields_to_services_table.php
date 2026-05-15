<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0)->after('logo_url');
            $table->boolean('is_available')->default(true)->after('is_visible');
            $table->text('payment_instructions')->nullable()->after('is_available');
        });

        if (Schema::hasColumn('services', 'script_code')) {
            Schema::table('services', function (Blueprint $table) {
                $table->longText('script_code')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['price', 'is_available', 'payment_instructions']);
        });
    }
};
