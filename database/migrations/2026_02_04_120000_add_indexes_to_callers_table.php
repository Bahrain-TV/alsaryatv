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
        Schema::table('callers', function (Blueprint $table) {
            $table->index('status');
            $table->index('ip_address');
            $table->index('phone');
            $table->index('is_winner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('callers', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['ip_address']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['is_winner']);
        });
    }
};
