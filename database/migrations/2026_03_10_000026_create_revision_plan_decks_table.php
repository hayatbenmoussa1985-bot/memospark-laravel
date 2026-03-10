<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revision_plan_decks', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained('revision_plans')->cascadeOnDelete();
            $table->foreignId('deck_id')->constrained()->cascadeOnDelete();

            $table->primary(['plan_id', 'deck_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revision_plan_decks');
    }
};
