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
                ->selectRaw("SUM(CASE WHEN question_type = 'number' THEN 1 ELSE 0 END) as number_answered_count")
                ->selectRaw("SUM(CASE WHEN question_type = 'number' AND is_correct = 1 THEN 1 ELSE 0 END) as number_correct_count")
                ->selectRaw("COALESCE(AVG(CASE WHEN question_type = 'number' THEN response_ms END), 0) as number_average_response_ms")
                ->selectRaw("SUM(CASE WHEN question_type = 'letter' THEN 1 ELSE 0 END) as letter_answered_count")
                ->selectRaw("SUM(CASE WHEN question_type = 'letter' AND is_correct = 1 THEN 1 ELSE 0 END) as letter_correct_count")
                ->selectRaw("COALESCE(AVG(CASE WHEN question_type = 'letter' THEN response_ms END), 0) as letter_average_response_ms")
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
                'number_answered_count' => (int) $summary->number_answered_count,
                'number_correct_count' => (int) $summary->number_correct_count,
                'number_average_response_ms' => (int) round((float) $summary->number_average_response_ms),
                'letter_answered_count' => (int) $summary->letter_answered_count,
                'letter_correct_count' => (int) $summary->letter_correct_count,
                'letter_average_response_ms' => (int) round((float) $summary->letter_average_response_ms),
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
