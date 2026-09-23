<?php

namespace App\Jobs;

use App\GenerationStatus;
use App\Models\QuestionGenerationRun;
use App\Models\QuizQuestion;
use App\Services\QuestionGenerator;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class GenerateQuizQuestions implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 600;

    /** @var array<int, int> */
    public array $backoff = [2, 10, 30];

    public function __construct(public int $generationRunId) {}

    public function uniqueId(): string
    {
        return (string) $this->generationRunId;
    }

    public function handle(QuestionGenerator $generator): void
    {
        $run = QuestionGenerationRun::query()->with('quiz')->findOrFail($this->generationRunId);
        $run->update([
            'status' => GenerationStatus::Generating,
            'generated_count' => 0,
            'error_message' => null,
            'started_at' => now(),
            'finished_at' => null,
        ]);

        $quiz = $run->quiz;
        $quiz->questions()->delete();
        $questions = $generator->generate($quiz->type, $run->requested_count, $run->number_count);
        $timestamp = now();

        foreach (array_chunk($questions, 25) as $chunk) {
            $rows = array_map(fn (array $question): array => [
                'uuid' => (string) Str::uuid(),
                'quiz_id' => $quiz->id,
                ...$question,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], $chunk);

            DB::transaction(function () use ($run, $rows): void {
                QuizQuestion::query()->insert($rows);
                $run->increment('generated_count', count($rows));
            });
        }

        $run->update(['status' => GenerationStatus::Validating]);
        $activeCount = $quiz->questions()->count();

        if ($activeCount !== $run->requested_count) {
            throw new \RuntimeException("Validasi generator gagal: {$activeCount}/{$run->requested_count} soal tersimpan.");
        }

        $quiz->update([
            'question_count' => $run->requested_count,
            'generation_config' => $run->configuration,
        ]);

        $run->update([
            'status' => GenerationStatus::Completed,
            'generated_count' => $activeCount,
            'finished_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        QuestionGenerationRun::query()->whereKey($this->generationRunId)->update([
            'status' => GenerationStatus::Failed,
            'error_message' => $exception?->getMessage() ?? 'Generator berhenti tanpa pesan kesalahan.',
            'finished_at' => now(),
        ]);
    }
}
