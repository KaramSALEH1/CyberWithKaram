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
        Schema::table('lessons', function (Blueprint $table) {
            // Drop the old obsolete column causing the 1364 error 
            if (Schema::hasColumn('lessons', 'youtube_url')) {
                $table->dropColumn('youtube_url');
            }
            
            // Ensure clean, nullable dynamic fields exist 
            if (!Schema::hasColumn('lessons', 'content')) {
                $table->longText('content')->nullable()->after('title');
            }
            if (!Schema::hasColumn('lessons', 'video_url')) {
                $table->string('video_url')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('lessons', 'video_path')) {
                $table->string('video_path')->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('lessons', 'video_type')) {
                $table->enum('video_type', ['youtube', 'local'])->default('youtube')->after('video_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            if (!Schema::hasColumn('lessons', 'youtube_url')) {
                $table->string('youtube_url')->nullable();
            }
            $table->dropColumn(['content', 'video_url', 'video_path', 'video_type']);
        });
    }
};
