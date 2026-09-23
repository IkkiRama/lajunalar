<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\User;
use App\QuizType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AttemptQuestionSelector
{
    /** @return array<int, int> */
    public function select(User $user, Quiz $quiz): array
    {
        $total = $quiz->question_count;
        $numberTarget = match ($quiz->type) {
            QuizType::Number => $total,
            QuizType::Letter => 0,
            QuizType::Mixed => (int) round($total * $quiz->number_percentage / 100),
        };
        $letterTarget = $total - $numberTarget;

        $numberIds = $this->leastSeen($user, $quiz, QuizType::Number, $numberTarget);
        $letterIds = $this->leastSeen($user, $quiz, QuizType::Letter, $letterTarget);

        if (count($numberIds) !== $numberTarget || count($letterIds) !== $letterTarget) {
            throw ValidationException::withMessages([
                'quiz' => "Bank soal belum mencukupi komposisi attempt: {$numberTarget} angka dan {$letterTarget} huruf.",
            ]);
        }

        return $this->balancedOrder($numberIds, $letterIds);
    }

    /** @return array<int, int> */
    private function leastSeen(User $user, Quiz $quiz, QuizType $type, int $count): array
    {
        if ($count === 0) {
            return [];
        }

        return $quiz->questions()
            ->where('type', $type)
            ->withCount([
                'attemptAnswers as user_seen_count' => fn (Builder $query) => $query
                    ->whereHas('attempt', fn (Builder $attempts) => $attempts->where('user_id', $user->id)),
            ])
            ->get(['id'])
            ->groupBy('user_seen_count')
            ->sortKeys()
            ->flatMap(fn (Collection $questions) => $questions->shuffle())
            ->take($count)
            ->pluck('id')
            ->map(fn (int $id): int => $id)
            ->all();
    }

    /**
     * Mix both types while preventing more than five identical types in a row
     * whenever questions of the other type are still available.
     *
     * @param  array<int, int>  $numberIds
     * @param  array<int, int>  $letterIds
     * @return array<int, int>
     */
    private function balancedOrder(array $numberIds, array $letterIds): array
    {
        shuffle($numberIds);
        shuffle($letterIds);
        $ordered = [];
        $lastType = null;
        $streak = 0;

        while ($numberIds !== [] || $letterIds !== []) {
            if ($numberIds === []) {
                $type = QuizType::Letter;
            } elseif ($letterIds === []) {
                $type = QuizType::Number;
            } elseif ($streak >= 5) {
                $type = $lastType === QuizType::Number ? QuizType::Letter : QuizType::Number;
            } else {
                $type = random_int(1, count($numberIds) + count($letterIds)) <= count($numberIds)
                    ? QuizType::Number
                    : QuizType::Letter;
            }

            $ordered[] = $type === QuizType::Number ? array_pop($numberIds) : array_pop($letterIds);
            $streak = $lastType === $type ? $streak + 1 : 1;
            $lastType = $type;
        }

        return $ordered;
    }
}
