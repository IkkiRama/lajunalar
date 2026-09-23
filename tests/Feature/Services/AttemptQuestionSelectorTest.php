<?php

namespace Tests\Feature\Services;

use App\AttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use App\QuizType;
use App\Services\AttemptQuestionSelector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptQuestionSelectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_mixed_attempt_has_configured_ratio_and_no_long_type_streak(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create([
            'type' => QuizType::Mixed,
            'question_count' => 20,
            'bank_size' => 40,
            'number_percentage' => 50,
        ]);
        $this->questions($quiz, QuizType::Number, 20, 1);
        $this->questions($quiz, QuizType::Letter, 20, 21);

        $selected = app(AttemptQuestionSelector::class)->select($user, $quiz);
        $types = QuizQuestion::query()->whereIn('id', $selected)->get(['id', 'type'])->keyBy('id');
        $orderedTypes = collect($selected)->map(fn (int $id): string => $types[$id]->type->value);

        $this->assertCount(20, $selected);
        $this->assertSame(10, $orderedTypes->filter(fn (string $type): bool => $type === 'number')->count());
        $this->assertSame(10, $orderedTypes->filter(fn (string $type): bool => $type === 'letter')->count());

        $longestStreak = 0;
        $currentStreak = 0;
        $previous = null;
        foreach ($orderedTypes as $type) {
            $currentStreak = $type === $previous ? $currentStreak + 1 : 1;
            $longestStreak = max($longestStreak, $currentStreak);
            $previous = $type;
        }
        $this->assertLessThanOrEqual(5, $longestStreak);
    }

    public function test_questions_not_seen_by_the_user_are_selected_first(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create([
            'type' => QuizType::Number,
            'question_count' => 5,
            'bank_size' => 10,
        ]);
        $questions = $this->questions($quiz, QuizType::Number, 10, 1);
        $seen = $questions->take(5);
        $attempt = QuizAttempt::factory()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'status' => AttemptStatus::Submitted,
            'question_order' => $seen->pluck('id')->all(),
            'finished_at' => now(),
        ]);

        foreach ($seen->values() as $index => $question) {
            QuizAttemptAnswer::query()->create([
                'quiz_attempt_id' => $attempt->id,
                'quiz_question_id' => $question->id,
                'position' => $index + 1,
                'question_type' => $question->type,
                'question_text' => $question->question_text,
                'answer_text' => $question->correct_answer,
                'correct_answer' => $question->correct_answer,
                'is_correct' => true,
                'response_ms' => 200,
                'answered_at' => now(),
            ]);
        }

        $selected = app(AttemptQuestionSelector::class)->select($user, $quiz);

        $this->assertEqualsCanonicalizing($questions->skip(5)->pluck('id')->all(), $selected);
    }

    private function questions(Quiz $quiz, QuizType $type, int $count, int $positionStart)
    {
        return collect(range(0, $count - 1))->map(fn (int $offset): QuizQuestion => QuizQuestion::factory()->create([
            'quiz_id' => $quiz->id,
            'position' => $positionStart + $offset,
            'type' => $type,
            'question_text' => $type === QuizType::Number
                ? '0 1 2 3 4 5 6 7 '.(($offset % 2) + 8)
                : 'A B C D E F G H I '.chr(74 + ($offset % 10)).' A',
            'correct_answer' => $type === QuizType::Number ? '9' : 'A',
        ]));
    }
}
