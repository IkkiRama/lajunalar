<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUuid;
use App\QuizType;
use Database\Factories\QuizQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['quiz_id', 'position', 'type', 'question_text', 'correct_answer', 'explanation_correct', 'signature'])]
class QuizQuestion extends Model
{
    /** @use HasFactory<QuizQuestionFactory> */
    use HasFactory, HasPublicUuid, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (self $question): void {
            $question->correct_answer = Str::upper($question->correct_answer);
            $compactPrompt = Str::of($question->question_text)->replace(' ', '')->upper()->toString();
            $type = $question->type instanceof QuizType ? $question->type->value : $question->type;
            $question->signature = hash('sha256', $type.'|'.$compactPrompt);
        });
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }

    protected function casts(): array
    {
        return ['type' => QuizType::class];
    }
}
