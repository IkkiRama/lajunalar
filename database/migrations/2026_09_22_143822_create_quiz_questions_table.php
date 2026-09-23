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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('type');
            $table->string('question_text', 100);
            $table->string('correct_answer', 1);
            $table->text('explanation_correct')->nullable();
            $table->string('signature', 64);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['quiz_id', 'position']);
            $table->index(['quiz_id', 'signature']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
