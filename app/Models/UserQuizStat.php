<?php

namespace App\Models;

use Database\Factories\UserQuizStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'quiz_id', 'attempts_count', 'total_score', 'best_score', 'total_correct', 'total_answered', 'last_attempt_at'])]
class UserQuizStat extends Model
{
    /** @use HasFactory<UserQuizStatFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    protected function casts(): array
    {
        return ['last_attempt_at' => 'datetime'];
    }
}
