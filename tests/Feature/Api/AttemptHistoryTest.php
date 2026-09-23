<?php

namespace Tests\Feature\Api;

use App\AttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttemptHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_paginate_all_of_their_attempt_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $quiz = Quiz::factory()->create();

        foreach (range(1, 16) as $index) {
            QuizAttempt::factory()->create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'status' => AttemptStatus::Submitted,
                'question_order' => range(1, 10),
                'score' => $index % 10,
                'answered_count' => 10,
                'correct_count' => $index % 10,
                'wrong_count' => 10 - ($index % 10),
                'accuracy' => ($index % 10) * 10,
                'started_at' => now()->subMinutes($index),
                'finished_at' => now()->subMinutes($index)->addMinute(),
            ]);
        }

        QuizAttempt::factory()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $otherUser->id,
            'status' => AttemptStatus::Submitted,
        ]);

        $this->actingAs($user)->getJson('/api/v1/history')
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 16)
            ->assertJsonPath('meta.per_page', 15)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('data.0.quiz.uuid', $quiz->uuid);

        $this->getJson('/api/v1/history?page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_history_finalizes_an_expired_in_progress_attempt(): void
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create();
        $attempt = QuizAttempt::factory()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'status' => AttemptStatus::InProgress,
            'question_order' => range(1, 10),
            'expires_at' => now()->subSecond(),
        ]);

        $this->actingAs($user)->getJson('/api/v1/history')
            ->assertOk()
            ->assertJsonPath('data.0.uuid', $attempt->uuid)
            ->assertJsonPath('data.0.status', AttemptStatus::Expired->value)
            ->assertJsonPath('data.0.unanswered_count', 10);

        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $attempt->id,
            'status' => AttemptStatus::Expired->value,
        ]);
    }
}
