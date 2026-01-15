<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cadastral_units', function (Blueprint $table) {
            $table->foreignId('cadastral_group_id')
                ->nullable()
                ->after('user_id')
                ->constrained('cadastral_groups')
                ->nullOnDelete();

            $table->index('cadastral_group_id');
        });

        // Drop the pivot table after data migration
        Schema::dropIfExists('cadastral_group_unit');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the pivot table
        Schema::create('cadastral_group_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadastral_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cadastral_unit_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['cadastral_group_id', 'cadastral_unit_id']);
        });

        Schema::table('cadastral_units', function (Blueprint $table) {
            $table->dropForeign(['cadastral_group_id']);
            $table->dropColumn('cadastral_group_id');
        });
    }
};
