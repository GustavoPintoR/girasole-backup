<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->boolean('all_day')->default(false);

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();

            $table->string('title')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->comment('ID dell\'utente a cui appartiene l\'evento');

            $table->index(['start', 'end']);
            $table->index(['start_date', 'end_date']);
            $table->index(['all_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
