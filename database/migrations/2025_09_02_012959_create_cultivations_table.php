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
        Schema::create('cultivations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nome della coltivazione');
            $table->text('description')->nullable()->comment('Descrizione della coltivazione');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultivations');
    }
};
