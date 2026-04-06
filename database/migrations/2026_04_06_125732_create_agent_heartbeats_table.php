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
        Schema::create('agent_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['online', 'offline', 'executing', 'degraded'])->default('online')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('os_type')->nullable();
            $table->string('agent_version')->nullable();
            $table->string('host_fingerprint')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('seen_at')->index();
            $table->timestamps();

            $table->index(['agent_id', 'seen_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_heartbeats');
    }
};
