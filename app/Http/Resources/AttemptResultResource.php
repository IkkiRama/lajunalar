<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResultResource extends JsonResource
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
            'score' => $this->score,
            'answered_count' => $this->answered_count,
            'correct_count' => $this->correct_count,
            'wrong_count' => $this->wrong_count,
            'unanswered_count' => $this->unanswered_count,
            'accuracy' => (float) $this->accuracy,
            'average_response_ms' => $this->average_response_ms,
            'started_at' => $this->started_at->toIso8601String(),
            'finished_at' => $this->finished_at?->toIso8601String(),
            'quiz' => [
                'uuid' => $this->quiz->uuid,
                'slug' => $this->quiz->slug,
                'title' => $this->quiz->title,
                'question_count' => count($this->question_order),
                'passing_score' => $this->quiz->passing_score,
                'passed' => $this->quiz->passing_score === null ? null : $this->score >= $this->quiz->passing_score,
            ],
            'answers' => $this->whenLoaded('reviewItems'),
        ];
    }
}
