<?php

namespace App\Models;

use Database\Factories\QuizAttemptAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quiz_attempt_id', 'quiz_question_id', 'position', 'question_type', 'question_text', 'answer_text', 'correct_answer', 'is_correct', 'response_ms', 'answered_at'])]
class QuizAttemptAnswer extends Model
{
    /** @use HasFactory<QuizAttemptAnswerFactory> */
    use HasFactory;

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }
}
