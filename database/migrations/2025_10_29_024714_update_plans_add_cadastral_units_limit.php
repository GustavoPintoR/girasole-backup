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
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('cadastral_units_number')->after('active')->nullable();
            $table->integer('field_groups_number')->after('cadastral_units_number')->nullable();
            $table->decimal('field_groups_max_area', 15, 2)->after('field_groups_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['cadastral_units_number', 'field_groups_number', 'field_groups_max_area']);
        });
    }
};
