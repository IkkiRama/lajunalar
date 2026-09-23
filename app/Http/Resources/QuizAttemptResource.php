<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizAttemptResource extends JsonResource
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
            'status' => $this->status->value,
            'next_position' => $this->next_position,
            'started_at' => $this->started_at->toIso8601String(),
            'expires_at' => $this->expires_at->toIso8601String(),
            'server_time' => now()->toIso8601String(),
            'quiz' => [
                'uuid' => $this->quiz->uuid,
                'slug' => $this->quiz->slug,
                'title' => $this->quiz->title,
                'type' => $this->quiz->type->value,
                'question_count' => $this->quiz->question_count,
                'duration_seconds' => $this->quiz->duration_seconds,
            ],
            'questions' => $this->whenLoaded('orderedQuestions', fn () => $this->orderedQuestions
                ->values()
                ->map(fn ($question, int $index): array => [
                    'position' => $index + 1,
                    'uuid' => $question->uuid,
                    'type' => $question->type->value,
                    'question_text' => $question->question_text,
                ])),
        ];
    }
}
