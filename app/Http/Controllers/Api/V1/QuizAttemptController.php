<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\FinalizeQuizAttempt;
use App\Actions\StartQuizAttempt;
use App\AttemptStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\QuizAttemptResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;

class QuizAttemptController extends Controller
{
    public function store(Quiz $quiz, StartQuizAttempt $start): QuizAttemptResource
    {
        $attempt = $start->handle(request()->user(), $quiz);

        return new QuizAttemptResource($this->loadOrderedQuestions($attempt));
    }

    public function show(QuizAttempt $attempt): QuizAttemptResource
    {
        abort_unless($attempt->user_id === request()->user()->id, 404);

        if ($attempt->status === AttemptStatus::InProgress && $attempt->expires_at->isPast()) {
            $attempt = app(FinalizeQuizAttempt::class)->handle($attempt, true);
        }

        return new QuizAttemptResource($this->loadOrderedQuestions($attempt));
    }

    public function finish(QuizAttempt $attempt, FinalizeQuizAttempt $finalize): JsonResponse
    {
        abort_unless($attempt->user_id === request()->user()->id, 404);
        $attempt = $finalize->handle($attempt, $attempt->expires_at->isPast());

        return response()->json([
            'data' => [
                'uuid' => $attempt->uuid,
                'status' => $attempt->status->value,
                'result_url' => "/attempt/{$attempt->uuid}/hasil",
            ],
        ]);
    }

    private function loadOrderedQuestions(QuizAttempt $attempt): QuizAttempt
    {
        $attempt->load(['quiz' => fn ($query) => $query
            ->withTrashed()
            ->select('id', 'uuid', 'title', 'slug', 'type', 'question_count', 'duration_seconds', 'deleted_at')]);

        if ($attempt->status !== AttemptStatus::InProgress) {
            return $attempt;
        }

        $questions = QuizQuestion::query()
            ->withTrashed()
            ->whereIn('id', $attempt->question_order)
            ->get(['id', 'uuid', 'type', 'question_text'])
            ->keyBy('id');
        $orderedQuestions = collect($attempt->question_order)
            ->map(fn (int $id) => $questions->get($id))
            ->filter()
            ->values();
        $attempt->setRelation('orderedQuestions', $orderedQuestions);

        return $attempt;
    }
}
