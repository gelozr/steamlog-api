<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->string('genre', 100)->nullable();
            $table->date('release_date')->nullable();
            $table->integer('steam_app_id')->nullable();
            $table->enum('enrichment_status', ['pending', 'in_progress', 'skipped', 'invalid', 'failed', 'done'])->default('skipped');
            $table->timestamps();

            $table->index(['enrichment_status', 'name']);
            $table->index(['genre', 'enrichment_status', 'release_date']);
            $table->index('steam_app_id');
            $table->unique(['steam_app_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
