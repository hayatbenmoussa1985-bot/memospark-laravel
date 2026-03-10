<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('review_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('study_sessions')->nullOnDelete();
            $table->unsignedTinyInteger('quality'); // 0-5
            $table->decimal('easiness_factor_before', 4, 2);
            $table->decimal('easiness_factor_after', 4, 2);
            $table->unsignedInteger('interval_before');
            $table->unsignedInteger('interval_after');
            $table->unsignedInteger('time_spent_ms')->nullable();
            $table->timestamp('reviewed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_logs');
    }
};
