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
        Schema::table('cadastral_groups', function (Blueprint $table) {
            $table->enum('creation_method', ['units', 'manual', 'import'])
                ->default('units')
                ->after('color');

            $table->text('original_geojson')->nullable()
                ->after('creation_method')
                ->comment('Original GeoJSON data for manual or imported groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cadastral_groups', function (Blueprint $table) {
            $table->dropColumn(['creation_method', 'original_geojson']);
        });
    }
};
