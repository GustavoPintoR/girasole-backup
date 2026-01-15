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
            $table->renameColumn('serial', 'serial_number')->nullable()->change();
            $table->string('urn')->after('serial')->nullable();
            $table->string('iccid')->after('urn')->nullable();
            $table->string('transmission_module_identification')->after('iccid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropColumn('urn');
            $table->dropColumn('iccid');
            $table->dropColumn('transmission_module_identification');
        });
    }
};
