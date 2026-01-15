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
        Schema::table('sensors', function (Blueprint $table) {
            $table->foreignId('sensor_type_id')
                ->after('serial')
                ->nullable()->constrained()->nullOnDelete();
            $table->string('firmware')->after('sensor_type_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropForeign('sensor_type_id');
            $table->dropColumn('sensor_type_id');
        });
    }
};
