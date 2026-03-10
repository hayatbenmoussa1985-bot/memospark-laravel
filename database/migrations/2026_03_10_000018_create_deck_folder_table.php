<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deck_folder', function (Blueprint $table) {
            $table->foreignId('deck_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folder_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);

            $table->primary(['deck_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deck_folder');
    }
};
