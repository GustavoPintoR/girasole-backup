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
        Schema::create('cultivars', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nome della cultivar');
            $table->text('description')->nullable()->comment('Descrizione della cultivar');
            $table->foreignId('cultivation_id')->constrained()->onDelete('cascade')->comment('ID della coltivazione associata');
            $table->timestamps();

            $table->unique(['name', 'cultivation_id'], 'cultivar_cultivation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultivars');
    }
};
