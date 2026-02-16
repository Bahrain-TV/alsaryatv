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
        Schema::create('obs_overlay_videos', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->unique();
            $table->string('path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->default('video/quicktime');
            $table->dateTime('recorded_at');
            $table->string('status')->default('ready'); // ready, archived, deleted
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('recorded_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('obs_overlay_videos');
    }
};
