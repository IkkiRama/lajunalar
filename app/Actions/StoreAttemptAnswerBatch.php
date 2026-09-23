<?php

namespace App\Actions;

use App\AttemptStatus;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\QuizType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreAttemptAnswerBatch
{
    /**
     * @param  array<int, array{position: int, question_uuid: string, answer: string, response_ms: int}>  $answers
     */
    public function handle(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        return DB::transaction(function () use ($attempt, $answers): QuizAttempt {
            $attempt = QuizAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($attempt->status !== AttemptStatus::InProgress) {
                throw ValidationException::withMessages(['attempt' => 'Pengerjaan ini sudah selesai.']);
            }

            if ($attempt->expires_at->isPast()) {
                app(FinalizeQuizAttempt::class)->handle($attempt, true);
                throw ValidationException::withMessages(['attempt' => 'Waktu pengerjaan sudah habis.']);
            }

            $answers = collect($answers)->sortBy('position')->values();
            $questionIds = $attempt->question_order;
            $questions = QuizQuestion::query()
                ->withTrashed()
                ->whereIn('id', $questionIds)
                ->get()
                ->keyBy('id');
            $expectedPosition = $attempt->next_position;
            $rows = [];
            $timestamp = now();

            foreach ($answers as $answer) {
                if ($answer['position'] < $expectedPosition) {
                    continue;
                }

                if ($answer['position'] !== $expectedPosition) {
                    throw ValidationException::withMessages(['answers' => 'Urutan jawaban tidak valid.']);
                }

                $questionId = $questionIds[$expectedPosition - 1] ?? null;
                $question = $questions->get($questionId);

                if ($question === null || $question->uuid !== $answer['question_uuid']) {
                    throw ValidationException::withMessages(['answers' => 'Soal tidak sesuai dengan urutan attempt.']);
                }

                $value = mb_strtoupper(trim($answer['answer']));
                $isValid = $question->type === QuizType::Number
                    ? preg_match('/^[0-9]$/', $value) === 1
                    : preg_match('/^[A-Z]$/', $value) === 1;

                if (! $isValid) {
                    throw ValidationException::withMessages([
                        'answers' => $question->type === QuizType::Number
                            ? 'Soal angka hanya menerima satu digit 0–9.'
                            : 'Soal huruf hanya menerima satu huruf A–Z.',
                    ]);
                }

                $rows[] = [
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'position' => $expectedPosition,
                    'question_type' => $question->type->value,
                    'question_text' => $question->question_text,
                    'answer_text' => $value,
                    'correct_answer' => $question->correct_answer,
                    'is_correct' => hash_equals($question->correct_answer, $value),
                    'response_ms' => min($answer['response_ms'], 300000),
                    'answered_at' => $timestamp,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
                $expectedPosition++;
            }

            if ($rows !== []) {
                QuizAttemptAnswer::query()->insert($rows);
                $attempt->update([
                    'next_position' => $expectedPosition,
                    'last_synced_at' => $timestamp,
                ]);
            }

            return $attempt->refresh();
        });
    }
}
