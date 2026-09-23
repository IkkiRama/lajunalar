<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\QuizType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<QuizQuestion> */
class QuizQuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'position' => fake()->unique()->numberBetween(1, 10000),
            'type' => QuizType::Number,
            'question_text' => '0 1 2 3 4 5 6 7 8',
            'correct_answer' => '9',
            'explanation_correct' => 'Angka yang hilang adalah 9.',
        ];
    }
}
