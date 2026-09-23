<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUuid;
use Database\Factories\QuestionReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quiz_question_id', 'quiz_attempt_id', 'user_id', 'reason', 'status', 'admin_note', 'resolved_by', 'resolved_at'])]
class QuestionReport extends Model
{
    /** @use HasFactory<QuestionReportFactory> */
    use HasFactory, HasPublicUuid;

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'quiz_attempt_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }
}
