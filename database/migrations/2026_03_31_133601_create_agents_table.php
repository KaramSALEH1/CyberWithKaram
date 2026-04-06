<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // صاحب الجهاز
            $table->string('agent_key')->unique(); // كود التفعيل الفريد
            $table->string('device_name'); // اسم جهاز الزبون
            $table->string('ip_address')->nullable();
            $table->string('os_type')->nullable(); // ويندوز، لينكس
            $table->enum('status', ['online', 'offline', 'executing'])->default('offline');
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
