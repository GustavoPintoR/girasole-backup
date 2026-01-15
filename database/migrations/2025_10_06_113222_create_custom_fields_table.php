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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->string('key')->unique();
            $table->string('label');
            $table->string('type')->default('text')->comment('text, textarea, number, date, select, etc.');
            $table->string('unit')->nullable();
            $table->boolean('is_required')->default(false);
            $table->text('description')->nullable();
            $table->json('options')->nullable()->comment('Options for select/radio types');
            $table->integer('order')->default(0)->comment('display order of the field in the form');
            $table->timestamps();

            $table->index(['model_type', 'key']);
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_id')->constrained()->onDelete('cascade');
            $table->morphs('fieldable'); // fieldable_id, fieldable_type
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['custom_field_id', 'fieldable_id', 'fieldable_type'], 'custom_field_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
