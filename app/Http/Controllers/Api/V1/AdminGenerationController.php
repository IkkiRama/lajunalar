<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\QueueQuestionGeneration;
use App\GenerationStatus;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateQuizQuestions;
use App\Models\QuestionGenerationRun;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminGenerationController extends Controller
{
    public function index(): JsonResponse
    {
        $runs = QuestionGenerationRun::query()
            ->with('quiz')
            ->latest()
            ->limit(20)
            ->get();

        return response()->json(['data' => $runs->map(fn (QuestionGenerationRun $run): array => $this->serialize($run))]);
    }

    public function store(Request $request, Quiz $quiz, QueueQuestionGeneration $queue): JsonResponse
    {
        $validated = $request->validate([
            'count' => ['required', 'integer', 'min:'.$quiz->question_count, 'max:5000'],
            'number_count' => ['nullable', 'integer', 'min:0', 'lte:count'],
        ]);

        $run = $queue->handle(
            $quiz,
            (int) $validated['count'],
            isset($validated['number_count']) ? (int) $validated['number_count'] : null,
            $request->user()->id,
        );

        return response()->json(['data' => $this->serialize($run->load('quiz'))], 202);
    }

    public function show(QuestionGenerationRun $generation): JsonResponse
    {
        return response()->json(['data' => $this->serialize($generation->load('quiz'))]);
    }

    public function resume(QuestionGenerationRun $generation): JsonResponse
    {
        if ($generation->status === GenerationStatus::Completed) {
            return response()->json(['data' => $this->serialize($generation->load('quiz'))]);
        }

        if ($generation->status === GenerationStatus::Failed) {
            $generation->update([
                'status' => GenerationStatus::Queued,
                'generated_count' => 0,
                'error_message' => null,
                'started_at' => null,
                'finished_at' => null,
            ]);
        }

        if ($generation->status === GenerationStatus::Queued) {
            GenerateQuizQuestions::dispatchAfterResponse($generation->id);
        }

        return response()->json(['data' => $this->serialize($generation->refresh()->load('quiz'))], 202);
    }

    /** @return array<string, mixed> */
    private function serialize(QuestionGenerationRun $run): array
    {
        $run->loadMissing('quiz');

        return [
            'uuid' => $run->uuid,
            'status' => $run->status->value,
            'status_label' => $run->status->label(),
            'requested_count' => $run->requested_count,
            'generated_count' => $run->generated_count,
            'number_count' => $run->number_count,
            'letter_count' => $run->letter_count,
            'percentage' => $run->requested_count > 0
                ? min(100, (int) round(($run->generated_count / $run->requested_count) * 100))
                : 0,
            'error_message' => $run->error_message,
            'started_at' => $run->started_at?->toIso8601String(),
            'finished_at' => $run->finished_at?->toIso8601String(),
            'created_at' => $run->created_at?->toIso8601String(),
            'quiz' => [
                'uuid' => $run->quiz->uuid,
                'title' => $run->quiz->title,
                'type' => $run->quiz->type->value,
                'type_label' => $run->quiz->type->label(),
                'status' => $run->quiz->status->value,
            ],
        ];
    }
}
