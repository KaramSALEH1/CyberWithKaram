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
        Schema::create('agent_command_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_command_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('result_status', ['succeeded', 'failed'])->index();
            $table->integer('exit_code')->nullable();
            $table->unsignedBigInteger('duration_ms')->nullable();
            $table->longText('stdout')->nullable();
            $table->longText('stderr')->nullable();
            $table->string('result_hash', 64)->nullable();
            $table->json('artifacts')->nullable();
            $table->timestamp('received_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_command_results');
    }
};
