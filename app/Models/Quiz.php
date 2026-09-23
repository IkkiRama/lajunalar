<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUuid;
use App\QuizStatus;
use App\QuizType;
use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['slug', 'title', 'description', 'type', 'status', 'question_count', 'bank_size', 'number_percentage', 'duration_seconds', 'passing_score', 'generation_config', 'starts_at', 'ends_at', 'published_at', 'created_by'])]
class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory, HasPublicUuid, SoftDeletes;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function generationRuns(): HasMany
    {
        return $this->hasMany(QuestionGenerationRun::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === QuizStatus::Published
            && ($this->starts_at === null || $this->starts_at->isPast())
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    protected function casts(): array
    {
        return [
            'type' => QuizType::class,
            'status' => QuizStatus::class,
            'generation_config' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }
}
