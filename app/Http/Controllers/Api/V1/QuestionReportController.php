<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionReportRequest;
use App\Models\QuestionReport;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;

class QuestionReportController extends Controller
{
    public function __invoke(StoreQuestionReportRequest $request, QuizQuestion $quizQuestion): JsonResponse
    {
        $attempt = null;

        if ($request->filled('attempt_uuid')) {
            $attempt = QuizAttempt::query()->where('uuid', $request->string('attempt_uuid'))->firstOrFail();
            abort_unless($attempt->user_id === $request->user()->id, 404);
            abort_unless($attempt->quiz_id === $quizQuestion->quiz_id, 422);
        }

        $report = QuestionReport::create([
            'quiz_question_id' => $quizQuestion->id,
            'quiz_attempt_id' => $attempt?->id,
            'user_id' => $request->user()->id,
            'reason' => $request->string('reason')->trim()->toString(),
        ]);

        return response()->json([
            'message' => 'Laporan soal berhasil dikirim.',
            'data' => ['uuid' => $report->uuid],
        ], 201);
    }
}
