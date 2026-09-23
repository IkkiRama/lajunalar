<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'question_count' => $this->question_count,
            'bank_size' => $this->bank_size,
            'number_percentage' => $this->number_percentage,
            'available_questions' => $this->whenCounted('questions'),
            'duration_seconds' => $this->duration_seconds,
            'passing_score' => $this->passing_score,
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'attempts' => $this->whenLoaded('attempts', fn () => $this->attempts->map(fn ($attempt): array => [
                'uuid' => $attempt->uuid,
                'score' => $attempt->score,
                'status' => $attempt->status->value,
                'answered_count' => $attempt->answered_count,
                'correct_count' => $attempt->correct_count,
                'wrong_count' => $attempt->wrong_count,
                'started_at' => $attempt->started_at->toIso8601String(),
                'finished_at' => $attempt->finished_at?->toIso8601String(),
            ])),
        ];
    }
}
