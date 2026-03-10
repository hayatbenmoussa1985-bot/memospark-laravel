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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('deck_id')->constrained()->cascadeOnDelete();
            $table->text('front_text');
            $table->text('back_text');
            $table->string('front_image_url', 500)->nullable();
            $table->string('back_image_url', 500)->nullable();
            $table->string('front_audio_url', 500)->nullable();
            $table->string('back_audio_url', 500)->nullable();
            $table->text('hint')->nullable();
            $table->text('explanation')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_mcq')->default(false);
            $table->text('mcq_question')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
