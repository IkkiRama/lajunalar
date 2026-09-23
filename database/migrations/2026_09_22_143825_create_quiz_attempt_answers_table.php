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
        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('position');
            $table->string('question_type');
            $table->string('question_text', 100);
            $table->string('answer_text', 1)->nullable();
            $table->string('correct_answer', 1);
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('response_ms')->default(0);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['quiz_attempt_id', 'position']);
            $table->index(['quiz_attempt_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
    }
};
