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
        Schema::table('billing_addresses', function (Blueprint $table) {
            $table->dropColumn('postal_code');
            $table->dropColumn('city');
            $table->dropColumn('province');

            $table->foreignId('region_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->foreignId('province_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->foreignId('postal_code_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_addresses', function (Blueprint $table) {
            //
        });
    }
};
