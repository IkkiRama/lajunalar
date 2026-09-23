<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\StoreAttemptAnswerBatch;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttemptAnswersRequest;
use App\Models\QuizAttempt;
use Illuminate\Http\JsonResponse;

class AttemptAnswerBatchController extends Controller
{
    public function __invoke(
        StoreAttemptAnswersRequest $request,
        QuizAttempt $attempt,
        StoreAttemptAnswerBatch $store,
    ): JsonResponse {
        abort_unless($attempt->user_id === $request->user()->id, 404);
        $attempt = $store->handle($attempt, $request->validated('answers'));

        return response()->json([
            'data' => [
                'next_position' => $attempt->next_position,
                'last_synced_at' => $attempt->last_synced_at?->toIso8601String(),
            ],
        ]);
    }
}
