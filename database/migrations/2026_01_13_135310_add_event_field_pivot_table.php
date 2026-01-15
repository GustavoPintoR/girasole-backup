<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cadastral_group_event', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('cadastral_group_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['event_id', 'cadastral_group_id']);

            $table->index('event_id');
            $table->index('cadastral_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cadastral_group_event');
    }
};
