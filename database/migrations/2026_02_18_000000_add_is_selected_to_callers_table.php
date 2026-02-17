<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add is_selected column to callers table.
     * - is_selected: set to true when a caller is randomly selected (via the spinner/random draw).
     *   Selected callers are excluded from future random draws.
     * - is_winner: remains as before — manually toggled by admin to confirm a winner.
     *
     * Flow: Random Draw → is_selected=true → Admin confirms → is_winner=true
     */
    public function up(): void
    {
        if (! Schema::hasColumn('callers', 'is_selected')) {
            Schema::table('callers', function (Blueprint $table) {
                $table->boolean('is_selected')->default(false)->after('is_winner')
                    ->comment('True when randomly selected by draw; excludes from future draws');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('callers', 'is_selected')) {
            Schema::table('callers', function (Blueprint $table) {
                $table->dropColumn('is_selected');
            });
        }
    }
};
