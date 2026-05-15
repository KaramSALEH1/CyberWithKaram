<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_heartbeat')->nullable()->index();
            $table->enum('status', ['online', 'offline'])->default('offline')->index();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_statuses');
    }
};
