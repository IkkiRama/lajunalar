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
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('in_progress');
            $table->json('question_order');
            $table->unsignedInteger('next_position')->default(1);
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('answered_count')->default(0);
            $table->unsignedInteger('correct_count')->default(0);
            $table->unsignedInteger('wrong_count')->default(0);
            $table->unsignedInteger('unanswered_count')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->unsignedInteger('average_response_ms')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'quiz_id', 'started_at']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
