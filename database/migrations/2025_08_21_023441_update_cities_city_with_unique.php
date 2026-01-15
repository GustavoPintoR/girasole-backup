<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropUnique('cities_name_unique');
            $table->dropUnique('cities_cadastral_code_unique');

            $table->unique(['name', 'cadastral_code'], 'cities_name_cadastral_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropUnique('cities_name_cadastral_unique');

            $table->unique('name', 'cities_name_unique');
            $table->unique('cadastral_code', 'cities_cadastral_code_unique');
        });
    }
};
