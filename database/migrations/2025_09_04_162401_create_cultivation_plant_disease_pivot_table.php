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
        Schema::create('cultivation_plant_disease', function (Blueprint $table) {
            $table->foreignId('cultivation_id')
                ->constrained('cultivations')
                ->cascadeOnDelete();

            $table->foreignId('plant_disease_id')
                ->constrained('plant_diseases')
                ->cascadeOnDelete();

            $table->primary(['cultivation_id', 'plant_disease_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultivation_plant_disease', function (Blueprint $table) {
            //
        });
    }
};
