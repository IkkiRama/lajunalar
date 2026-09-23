<?php

namespace App\Models;

use App\GenerationStatus;
use App\Models\Concerns\HasPublicUuid;
use Database\Factories\QuestionGenerationRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['quiz_id', 'created_by', 'requested_count', 'generated_count', 'number_count', 'letter_count', 'status', 'configuration', 'error_message', 'started_at', 'finished_at'])]
class QuestionGenerationRun extends Model
{
    /** @use HasFactory<QuestionGenerationRunFactory> */
    use HasFactory, HasPublicUuid;

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected function casts(): array
    {
        return [
            'status' => GenerationStatus::class,
            'configuration' => 'array',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
