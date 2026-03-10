<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->decimal('easiness_factor', 4, 2)->default(2.50);
            $table->unsignedInteger('interval_days')->default(0);
            $table->unsignedInteger('repetitions')->default(0);
            $table->timestamp('next_review_at')->useCurrent();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->unsignedInteger('total_reviews')->default(0);
            $table->unsignedInteger('correct_reviews')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'card_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_progress');
    }
};
