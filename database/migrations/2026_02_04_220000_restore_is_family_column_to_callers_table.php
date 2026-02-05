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
            if (! Schema::hasColumn('callers', 'is_family')) {
                $table->boolean('is_family')->default(false)->after('is_winner');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('callers', function (Blueprint $table) {
            if (Schema::hasColumn('callers', 'is_family')) {
                $table->dropColumn('is_family');
            }
        });
    }
};
