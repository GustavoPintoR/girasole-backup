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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nome del comune');
            $table->string('cadastral_code', 4)->unique()->comment('Codice catastale del comune (4 caratteri)');
            $table->foreignId('province_id')->constrained()->onDelete('cascade')->comment('ID della provincia associata');
            $table->foreignId('region_id')->constrained()->onDelete('cascade')->comment('ID della regione associata');
            $table->timestamps();

            // Indice unico combinato su nome e provincia (un comune può avere lo stesso nome in province diverse)
            // $table->unique(['name', 'province_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
