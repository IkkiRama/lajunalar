<?php

namespace App\Actions;

use App\AttemptStatus;
use App\Models\QuizAttempt;
use App\Models\UserQuizStat;
use Illuminate\Support\Facades\DB;

class FinalizeQuizAttempt
{
    public function handle(QuizAttempt $attempt, bool $expired = false): QuizAttempt
    {
        return DB::transaction(function () use ($attempt, $expired): QuizAttempt {
            $attempt = QuizAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($attempt->status !== AttemptStatus::InProgress) {
                return $attempt;
            }

            $summary = $attempt->answers()
                ->selectRaw('COUNT(*) as answered_count')
                ->selectRaw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_count')
                ->selectRaw('COALESCE(AVG(response_ms), 0) as average_response_ms')
                ->first();

            $answeredCount = (int) $summary->answered_count;
            $correctCount = (int) $summary->correct_count;
            $wrongCount = $answeredCount - $correctCount;
            $questionCount = count($attempt->question_order);
            $accuracy = $answeredCount > 0 ? round(($correctCount / $answeredCount) * 100, 2) : 0;

            $attempt->update([
                'status' => $expired ? AttemptStatus::Expired : AttemptStatus::Submitted,
                'score' => $correctCount,
                'answered_count' => $answeredCount,
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
                'unanswered_count' => max(0, $questionCount - $answeredCount),
                'accuracy' => $accuracy,
                'average_response_ms' => (int) round((float) $summary->average_response_ms),
                'finished_at' => now(),
            ]);

            $stat = UserQuizStat::query()
                ->where('user_id', $attempt->user_id)
                ->where('quiz_id', $attempt->quiz_id)
                ->lockForUpdate()
                ->first();

            if ($stat === null) {
                UserQuizStat::create([
                    'user_id' => $attempt->user_id,
                    'quiz_id' => $attempt->quiz_id,
                    'attempts_count' => 1,
                    'total_score' => $correctCount,
                    'best_score' => $correctCount,
                    'total_correct' => $correctCount,
                    'total_answered' => $answeredCount,
                    'last_attempt_at' => $attempt->finished_at,
                ]);
            } else {
                $stat->update([
                    'attempts_count' => $stat->attempts_count + 1,
                    'total_score' => $stat->total_score + $correctCount,
                    'best_score' => max($stat->best_score, $correctCount),
                    'total_correct' => $stat->total_correct + $correctCount,
                    'total_answered' => $stat->total_answered + $answeredCount,
                    'last_attempt_at' => $attempt->finished_at,
                ]);
            }

            return $attempt->refresh();
        });
    }
}
