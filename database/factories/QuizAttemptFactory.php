<?php

namespace Database\Factories;

use App\AttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<QuizAttempt> */
class QuizAttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'user_id' => User::factory(),
            'status' => AttemptStatus::InProgress,
            'question_order' => [],
            'next_position' => 1,
            'started_at' => now(),
            'expires_at' => now()->addMinutes(5),
        ];
    }
}
