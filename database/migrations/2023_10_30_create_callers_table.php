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
        // Only create the table if it doesn't exist
        if (! Schema::hasTable('callers')) {
            Schema::create('callers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone');
                $table->string('cpr', 150)->unique()->comment('Civil Personal Registeration Number - Bahraini ID');

                $table->boolean('is_family')->default(false);
                $table->string('ip_address')->nullable();
                $table->timestamp('last_hit')->nullable(); // This line is correct now
                $table->integer('hits')->default(0);
                $table->string('status')->default('active');
                $table->string('level')->default('bronze');

                $table->text('notes')->nullable();
                $table->boolean('is_winner')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Add any missing columns to the existing table
            Schema::table('callers', function (Blueprint $table) {
                // add the level field
                if (! Schema::hasColumn('callers', 'level')) {
                    $table->string('level')->default('bronze');
                }
                // if (!Schema::hasColumn('callers', 'region')) {
                //     $table->string('region')->nullable();
                // }
                // if (!Schema::hasColumn('callers', 'notes')) {
                //     $table->text('notes')->nullable();
                // }
                // if (!Schema::hasColumn('callers', 'is_winner')) {
                //     $table->boolean('is_winner')->default(false);
                // }
                // if (!Schema::hasColumn('callers', 'deleted_at')) {
                //     $table->softDeletes();
                // }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('callers');
    }
};
