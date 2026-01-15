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
        Schema::create('billing_infos', function (Blueprint $table) {
            $table->id();
            $table->string('fiscal_type');
            $table->string('business_name')->nullable();
            $table->string('sdi')->nullable();
            $table->string('fiscal_code');
            $table->string('vat_number')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_infos');
    }
};
