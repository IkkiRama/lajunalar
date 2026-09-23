<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\FinalizeQuizAttempt;
use App\AttemptStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttemptHistoryController extends Controller
{
    public function __invoke(Request $request, FinalizeQuizAttempt $finalize): JsonResponse
    {
        $user = $request->user();

        $user->attempts()
            ->where('status', AttemptStatus::InProgress)
            ->where('expires_at', '<=', now())
            ->get()
            ->each(fn ($attempt) => $finalize->handle($attempt, true));

        $attempts = $user->attempts()
            ->with(['quiz' => fn ($query) => $query
                ->withTrashed()
                ->select('id', 'uuid', 'title', 'type')])
            ->latest('started_at')
            ->paginate(15);

        return response()->json([
            'data' => $attempts->getCollection()->map(fn ($attempt): array => [
                'uuid' => $attempt->uuid,
                'status' => $attempt->status->value,
                'status_label' => $attempt->status->getLabel(),
                'score' => $attempt->score,
                'answered_count' => $attempt->answered_count,
                'progress_count' => $attempt->status === AttemptStatus::InProgress
                    ? max(0, $attempt->next_position - 1)
                    : $attempt->answered_count,
                'correct_count' => $attempt->correct_count,
                'wrong_count' => $attempt->wrong_count,
                'unanswered_count' => $attempt->unanswered_count,
                'accuracy' => (float) $attempt->accuracy,
                'question_count' => count($attempt->question_order),
                'started_at' => $attempt->started_at->toIso8601String(),
                'finished_at' => $attempt->finished_at?->toIso8601String(),
                'expires_at' => $attempt->expires_at->toIso8601String(),
                'quiz' => [
                    'uuid' => $attempt->quiz->uuid,
                    'title' => $attempt->quiz->title,
                    'type' => $attempt->quiz->type->value,
                    'type_label' => $attempt->quiz->type->label(),
                ],
            ])->values(),
            'meta' => [
                'current_page' => $attempts->currentPage(),
                'last_page' => $attempts->lastPage(),
                'per_page' => $attempts->perPage(),
                'total' => $attempts->total(),
                'from' => $attempts->firstItem(),
                'to' => $attempts->lastItem(),
            ],
        ]);
    }
}
