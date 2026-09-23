<?php

namespace App\Models;

use App\AttemptStatus;
use App\Models\Concerns\HasPublicUuid;
use Database\Factories\QuizAttemptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['quiz_id', 'user_id', 'status', 'question_order', 'next_position', 'score', 'answered_count', 'correct_count', 'wrong_count', 'unanswered_count', 'accuracy', 'average_response_ms', 'started_at', 'expires_at', 'finished_at', 'last_synced_at'])]
class QuizAttempt extends Model
{
    /** @use HasFactory<QuizAttemptFactory> */
    use HasFactory, HasPublicUuid;

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class)->orderBy('position');
    }

    protected function casts(): array
    {
        return [
            'status' => AttemptStatus::class,
            'question_order' => 'array',
            'accuracy' => 'decimal:2',
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'finished_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }
}
