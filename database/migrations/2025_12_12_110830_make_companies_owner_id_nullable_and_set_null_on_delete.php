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
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);

            $table->foreignId('owner_id')->nullable()->change();

            // Re-add foreign key with nullOnDelete
            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropIndex(['owner_id']);
            $table->foreignId('owner_id')->nullable(false)->change();

            // Re-add foreign key with cascadeOnDelete
            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
