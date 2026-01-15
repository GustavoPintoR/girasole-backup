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
        Schema::table('forecast_logs', function (Blueprint $table) {
            $table->dateTime('ran_at')->after('data')->nullable();
            $table->string('parameters')->after('ran_at')->nullable();
            $table->json('data')->nullable()->change();
            $table->unique(['field_id', 'ran_at', 'parameters']);
            $table->index(['ran_at', 'field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('forecast_logs', function (Blueprint $table) {
            //
        });
    }
};
