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
        Schema::create('cadastral_group_cultivar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cadastral_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cultivar_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['cadastral_group_id', 'cultivar_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cadastral_group_cultivar');
    }
};
