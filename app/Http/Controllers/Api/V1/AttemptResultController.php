<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\FinalizeQuizAttempt;
use App\AttemptStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttemptResultResource;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;

class AttemptResultController extends Controller
{
    public function __invoke(QuizAttempt $attempt, FinalizeQuizAttempt $finalize): AttemptResultResource
    {
        abort_unless($attempt->user_id === request()->user()->id, 404);

        if ($attempt->status === AttemptStatus::InProgress && $attempt->expires_at->isPast()) {
            $attempt = $finalize->handle($attempt, true);
        }

        abort_if($attempt->status === AttemptStatus::InProgress, 409, 'Pengerjaan masih berlangsung.');
        $attempt->load(['quiz', 'answers']);
        $questions = QuizQuestion::query()
            ->withTrashed()
            ->whereIn('id', $attempt->question_order)
            ->get(['id', 'uuid', 'type', 'question_text', 'correct_answer', 'explanation_correct'])
            ->keyBy('id');
        $answers = $attempt->answers->keyBy('position');
        $reviewItems = collect($attempt->question_order)->map(function (int $questionId, int $index) use ($questions, $answers): array {
            $position = $index + 1;
            $question = $questions->get($questionId);
            $answer = $answers->get($position);

            return [
                'position' => $position,
                'question_uuid' => $question?->uuid,
                'question_type' => $question?->type->value ?? $answer?->question_type,
                'question_text' => $question?->question_text ?? $answer?->question_text,
                'answer_text' => $answer?->answer_text,
                'correct_answer' => $answer?->correct_answer ?? $question?->correct_answer,
                'is_correct' => $answer?->is_correct ?? false,
                'response_ms' => $answer?->response_ms,
                'explanation' => $question?->explanation_correct
                    ?? ($answer ? "Jawaban yang benar adalah {$answer->correct_answer}." : null),
            ];
        });
        $attempt->setRelation('reviewItems', $reviewItems);

        return new AttemptResultResource($attempt);
    }
}
