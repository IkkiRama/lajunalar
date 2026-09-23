<?php

namespace App\Actions;

use App\AttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartQuizAttempt
{
    public function handle(User $user, Quiz $quiz): QuizAttempt
    {
        if (! $quiz->isAvailable()) {
            throw ValidationException::withMessages([
                'quiz' => 'Paket belum tersedia atau sudah ditutup.',
            ]);
        }

        return DB::transaction(function () use ($user, $quiz): QuizAttempt {
            $activeAttempt = QuizAttempt::query()
                ->whereBelongsTo($user)
                ->whereBelongsTo($quiz)
                ->where('status', AttemptStatus::InProgress)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($activeAttempt !== null && $activeAttempt->expires_at->isFuture()) {
                return $activeAttempt;
            }

            if ($activeAttempt !== null) {
                app(FinalizeQuizAttempt::class)->handle($activeAttempt, true);
            }

            $questionIds = $quiz->questions()
                ->inRandomOrder()
                ->limit($quiz->question_count)
                ->pluck('id')
                ->all();

            if (count($questionIds) < $quiz->question_count) {
                throw ValidationException::withMessages([
                    'quiz' => "Paket belum memiliki {$quiz->question_count} soal aktif.",
                ]);
            }

            $startedAt = now();

            return QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'status' => AttemptStatus::InProgress,
                'question_order' => $questionIds,
                'next_position' => 1,
                'started_at' => $startedAt,
                'expires_at' => $startedAt->copy()->addSeconds($quiz->duration_seconds),
            ]);
        });
    }
}
