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
        Schema::create('user_quiz_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('attempts_count')->default(0);
            $table->unsignedBigInteger('total_score')->default(0);
            $table->unsignedInteger('best_score')->default(0);
            $table->unsignedBigInteger('total_correct')->default(0);
            $table->unsignedBigInteger('total_answered')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'quiz_id']);
            $table->index(['user_id', 'last_attempt_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_quiz_stats');
    }
};
