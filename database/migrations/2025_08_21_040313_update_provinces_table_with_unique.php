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
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropUnique('provinces_name_unique');
            $table->dropUnique('provinces_code_unique');

            $table->unique(['name', 'code'], 'provinces_code_unique_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropUnique('provinces_code_unique_name_unique');

            $table->unique('name', 'provinces_name_unique');
            $table->unique('code', 'provinces_code_unique');
        });
    }
};
