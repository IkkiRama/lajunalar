<?php

namespace Tests\Feature\Services;

use App\QuizType;
use App\Services\QuestionGenerator;
use Tests\TestCase;

class QuestionGeneratorTest extends TestCase
{
    public function test_it_generates_valid_missing_number_questions(): void
    {
        $questions = app(QuestionGenerator::class)->generate(QuizType::Number, 75);

        $this->assertCount(75, $questions);
        $this->assertCount(75, array_unique(array_column($questions, 'signature')));

        foreach ($questions as $question) {
            $digits = explode(' ', $question['question_text']);
            $this->assertCount(9, $digits);
            $this->assertCount(9, array_unique($digits));
            $this->assertNotContains($question['correct_answer'], $digits);
        }
    }

    public function test_it_generates_valid_duplicate_letter_questions(): void
    {
        $questions = app(QuestionGenerator::class)->generate(QuizType::Letter, 75);

        foreach ($questions as $question) {
            $letters = explode(' ', $question['question_text']);
            $counts = array_count_values($letters);
            $this->assertCount(10, $letters);
            $this->assertSame(2, $counts[$question['correct_answer']]);
            $this->assertCount(1, array_filter($counts, fn (int $count): bool => $count === 2));
        }
    }

    public function test_mixed_generation_respects_the_requested_split(): void
    {
        $questions = app(QuestionGenerator::class)->generate(QuizType::Mixed, 100, 37);
        $types = array_count_values(array_column($questions, 'type'));

        $this->assertSame(37, $types[QuizType::Number->value]);
        $this->assertSame(63, $types[QuizType::Letter->value]);
    }
}
