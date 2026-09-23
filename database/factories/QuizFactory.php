<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\QuizStatus;
use App\QuizType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Quiz> */
class QuizFactory extends Factory
{
    public function definition(): array
    {
        $title = 'Paket '.fake()->unique()->words(2, true);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'title' => Str::title($title),
            'description' => fake()->sentence(),
            'type' => QuizType::Mixed,
            'status' => QuizStatus::Published,
            'question_count' => 10,
            'duration_seconds' => 300,
            'published_at' => now(),
        ];
    }
}
