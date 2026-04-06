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
        Schema::create('agent_commands', function (Blueprint $table) {
            $table->id();
            $table->uuid('command_uuid')->unique();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('command_key');
            $table->json('payload')->nullable();
            $table->string('signature_hash', 64);
            $table->string('nonce')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->enum('status', ['queued', 'sent', 'running', 'succeeded', 'failed', 'expired', 'cancelled'])->default('queued')->index();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_commands');
    }
};
