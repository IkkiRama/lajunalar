<?php

namespace App\Actions;

use App\GenerationStatus;
use App\Jobs\GenerateQuizQuestions;
use App\Models\QuestionGenerationRun;
use App\Models\Quiz;
use App\QuizType;
use Illuminate\Validation\ValidationException;

class QueueQuestionGeneration
{
    public function handle(Quiz $quiz, int $count, ?int $numberCount, int $userId): QuestionGenerationRun
    {
        if ($count < $quiz->question_count || $count > 5000) {
            throw ValidationException::withMessages(['count' => "Ukuran bank harus minimal {$quiz->question_count} dan maksimal 5.000 soal."]);
        }

        $hasActiveRun = $quiz->generationRuns()
            ->whereIn('status', [GenerationStatus::Queued, GenerationStatus::Generating, GenerationStatus::Validating])
            ->exists();

        if ($hasActiveRun) {
            throw ValidationException::withMessages(['quiz_id' => 'Generator untuk paket ini masih berjalan.']);
        }

        if ($quiz->type === QuizType::Mixed && ($numberCount === null || $numberCount < 0 || $numberCount > $count)) {
            throw ValidationException::withMessages(['number_count' => 'Jumlah soal angka tidak boleh melebihi total soal.']);
        }

        $resolvedNumberCount = match ($quiz->type) {
            QuizType::Number => $count,
            QuizType::Letter => 0,
            QuizType::Mixed => $numberCount,
        };

        $run = $quiz->generationRuns()->create([
            'created_by' => $userId,
            'requested_count' => $count,
            'number_count' => $resolvedNumberCount,
            'letter_count' => $count - $resolvedNumberCount,
            'status' => GenerationStatus::Queued,
            'configuration' => [
                'replace_existing' => true,
                'quiz_type' => $quiz->type->value,
                'number_percentage' => $quiz->number_percentage,
            ],
        ]);

        // Generation is intentionally executed after the HTTP response. This keeps
        // the React progress flow asynchronous without requiring a queue worker for
        // this small, admin-only workload.
        GenerateQuizQuestions::dispatchAfterResponse($run->id);

        return $run;
    }
}
