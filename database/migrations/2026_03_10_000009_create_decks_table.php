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
        Schema::create('decks', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('language', 50)->default('en');
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->enum('visibility', ['private', 'public', 'library'])->default('private');
            $table->string('cover_image_path', 500)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('cards_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->boolean('is_ai_generated')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decks');
    }
};
