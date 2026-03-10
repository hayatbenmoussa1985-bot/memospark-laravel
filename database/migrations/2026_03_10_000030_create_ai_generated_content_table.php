<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generated_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deck_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('card_id')->nullable()->constrained()->nullOnDelete();
            $table->text('prompt_used');
            $table->string('image_url', 500)->nullable();
            $table->string('generation_source', 100);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generated_content');
    }
};
