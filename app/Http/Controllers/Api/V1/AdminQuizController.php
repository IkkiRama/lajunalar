<?php

namespace App\Http\Controllers\Api\V1;

use App\GenerationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveQuizRequest;
use App\Models\Quiz;
use App\QuizStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class AdminQuizController extends Controller
{
    public function index(): JsonResponse
    {
        $quizzes = Quiz::query()
            ->withCount('questions')
            ->with(['generationRuns' => fn ($query) => $query->latest()->limit(1)])
            ->latest()
            ->get();

        return response()->json([
            'data' => $quizzes->map(fn (Quiz $quiz): array => $this->serialize($quiz)),
            'meta' => [
                'total' => $quizzes->count(),
                'published' => $quizzes->where('status', QuizStatus::Published)->count(),
                'questions' => $quizzes->sum('questions_count'),
            ],
        ]);
    }

    public function store(SaveQuizRequest $request): JsonResponse
    {
        $quiz = Quiz::query()->create([
            ...$this->attributes($request),
            'slug' => $this->uniqueSlug($request->string('title')->toString()),
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $this->serialize($quiz->loadCount('questions'))], 201);
    }

    public function update(SaveQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $title = $request->string('title')->toString();
        $attributes = $this->attributes($request);

        if ($title !== $quiz->title) {
            $attributes['slug'] = $this->uniqueSlug($title, $quiz);
        }

        $quiz->update($attributes);

        return response()->json(['data' => $this->serialize($quiz->refresh()->loadCount('questions'))]);
    }

    public function destroy(Quiz $quiz): JsonResponse
    {
        $quiz->delete();

        return response()->json(['message' => 'Paket berhasil dihapus.']);
    }

    /** @return array<string, mixed> */
    private function attributes(SaveQuizRequest $request): array
    {
        $status = QuizStatus::from($request->string('status')->toString());

        return [
            'title' => $request->string('title')->toString(),
            'description' => $request->input('description'),
            'type' => $request->string('type')->toString(),
            'status' => $status,
            'question_count' => $request->integer('question_count'),
            'duration_seconds' => (int) round($request->float('duration_minutes') * 60),
            'passing_score' => $request->input('passing_score'),
            'starts_at' => $request->date('starts_at'),
            'ends_at' => $request->date('ends_at'),
            'published_at' => $status === QuizStatus::Published ? now() : null,
        ];
    }

    private function uniqueSlug(string $title, ?Quiz $except = null): string
    {
        $base = Str::slug($title) ?: 'paket-latihan';
        $slug = $base;
        $suffix = 2;

        while (Quiz::withTrashed()
            ->where('slug', $slug)
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /** @return array<string, mixed> */
    private function serialize(Quiz $quiz): array
    {
        $latestRun = $quiz->relationLoaded('generationRuns') ? $quiz->generationRuns->first() : null;
        $activeStatuses = [GenerationStatus::Queued, GenerationStatus::Generating, GenerationStatus::Validating];

        return [
            'uuid' => $quiz->uuid,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'type' => $quiz->type->value,
            'type_label' => $quiz->type->label(),
            'status' => $quiz->status->value,
            'status_label' => $quiz->status->label(),
            'question_count' => $quiz->question_count,
            'available_questions' => (int) ($quiz->questions_count ?? $quiz->questions()->count()),
            'duration_seconds' => $quiz->duration_seconds,
            'duration_minutes' => $quiz->duration_seconds / 60,
            'passing_score' => $quiz->passing_score,
            'starts_at' => $quiz->starts_at?->toIso8601String(),
            'ends_at' => $quiz->ends_at?->toIso8601String(),
            'latest_generation' => $latestRun ? [
                'uuid' => $latestRun->uuid,
                'status' => $latestRun->status->value,
                'generated_count' => $latestRun->generated_count,
                'requested_count' => $latestRun->requested_count,
            ] : null,
            'generation_active' => $latestRun && in_array($latestRun->status, $activeStatuses, true),
            'created_at' => $quiz->created_at?->toIso8601String(),
            'updated_at' => $quiz->updated_at?->toIso8601String(),
        ];
    }
}
