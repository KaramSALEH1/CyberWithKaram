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
        Schema::table('agents', function (Blueprint $table) {
            $table->string('api_token_hash', 64)->nullable()->unique()->after('agent_key');
            $table->timestamp('token_last_rotated_at')->nullable()->after('api_token_hash');
            $table->string('last_nonce')->nullable()->after('token_last_rotated_at');
            $table->string('agent_version')->nullable()->after('os_type');
            $table->string('host_fingerprint')->nullable()->after('agent_version');
            $table->json('metadata')->nullable()->after('host_fingerprint');
            $table->timestamp('registered_at')->nullable()->after('last_seen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn([
                'api_token_hash',
                'token_last_rotated_at',
                'last_nonce',
                'agent_version',
                'host_fingerprint',
                'metadata',
                'registered_at',
            ]);
        });
    }
};
