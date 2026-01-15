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
        Schema::create('cadastral_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('section', 1)->nullable();
            $table->string('sheet', 6);
            $table->string('parcel', 10);
            $table->string('sub', 4)->nullable();
            $table->string('category', 3)->nullable();
            $table->string('class', 2)->nullable();
            $table->decimal('cadastral_area', 10, 2)->nullable();
            $table->decimal('dominical_income', 10, 2)->nullable();
            $table->decimal('agrarian_income', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('geometry_source')->nullable();
            $table->geometry('geometry', subtype: 'POLYGON', srid: 4326)->nullable();
            $table->geometry('centroid', subtype: 'POINT', srid: 4326)->nullable();
            $table->timestamp('geometry_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['city_id', 'section', 'sheet', 'parcel', 'sub'], 'cadastral_units_unique');
//            $table->spatialIndex('geometry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cadastral_units');
    }
};
