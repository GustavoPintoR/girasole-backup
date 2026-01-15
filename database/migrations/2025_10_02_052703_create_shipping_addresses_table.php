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
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number');
            $table->text('address');
            $table->boolean('same_as_billing')->default(false);
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate();
            $table->foreignId('region_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->foreignId('province_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnUpdate();
            $table->foreignId('postal_code_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};
