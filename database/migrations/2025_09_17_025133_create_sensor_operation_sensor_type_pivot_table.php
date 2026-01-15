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
        Schema::create('sensor_operation_sensor_type', function (Blueprint $table) {
            $table->foreignId('sensor_operation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sensor_type_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensor_operation_sensor_type', function (Blueprint $table) {
            $table->dropForeign('sensor_operation_id');
            $table->dropForeign('sensor_type_id');
        });
    }
};
