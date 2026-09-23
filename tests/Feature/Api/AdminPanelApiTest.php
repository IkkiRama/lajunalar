<?php

namespace Tests\Feature\Api;

use App\Jobs\GenerateQuizQuestions;
use App\Models\Quiz;
use App\Models\User;
use App\QuizStatus;
use App\QuizType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class AdminPanelApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_an_administrator_can_open_the_react_admin_api(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/v1/admin/quizzes')
            ->assertForbidden();
    }

    public function test_administrator_can_create_and_update_a_quiz(): void
    {
        $admin = User::factory()->administrator()->create();

        $created = $this->actingAs($admin)->postJson('/api/v1/admin/quizzes', [
            'title' => 'Paket React Admin',
            'description' => 'Paket yang dibuat dari panel React.',
            'type' => QuizType::Mixed->value,
            'status' => QuizStatus::Published->value,
            'question_count' => 500,
            'bank_size' => 1500,
            'number_percentage' => 50,
            'duration_minutes' => 5,
            'passing_score' => 70,
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Paket React Admin')
            ->assertJsonPath('data.duration_seconds', 300);

        $uuid = $created->json('data.uuid');

        $this->actingAs($admin)->putJson("/api/v1/admin/quizzes/{$uuid}", [
            'title' => 'Paket React Admin Diperbarui',
            'description' => 'Deskripsi baru.',
            'type' => QuizType::Number->value,
            'status' => QuizStatus::Draft->value,
            'question_count' => 250,
            'bank_size' => 1000,
            'number_percentage' => 100,
            'duration_minutes' => 4,
            'passing_score' => null,
        ])->assertOk()
            ->assertJsonPath('data.type', QuizType::Number->value)
            ->assertJsonPath('data.question_count', 250)
            ->assertJsonPath('data.bank_size', 1000)
            ->assertJsonPath('data.duration_seconds', 240);
    }

    public function test_administrator_can_start_generation_from_the_react_panel(): void
    {
        Bus::fake();
        $admin = User::factory()->administrator()->create();
        $quiz = Quiz::factory()->create(['type' => QuizType::Mixed]);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/quizzes/{$quiz->uuid}/generations", [
            'count' => 500,
            'number_count' => 225,
        ])->assertAccepted()
            ->assertJsonPath('data.requested_count', 500)
            ->assertJsonPath('data.number_count', 225)
            ->assertJsonPath('data.letter_count', 275)
            ->assertJsonPath('data.status', 'queued');

        $uuid = $response->json('data.uuid');

        $this->actingAs($admin)
            ->getJson("/api/v1/admin/generations/{$uuid}")
            ->assertOk()
            ->assertJsonPath('data.quiz.uuid', $quiz->uuid);

        Bus::assertDispatchedAfterResponse(GenerateQuizQuestions::class);
    }

    public function test_generation_finishes_without_a_separate_queue_worker(): void
    {
        $admin = User::factory()->administrator()->create();
        $quiz = Quiz::factory()->create(['type' => QuizType::Mixed]);

        $response = $this->actingAs($admin)->postJson("/api/v1/admin/quizzes/{$quiz->uuid}/generations", [
            'count' => 40,
            'number_count' => 18,
        ])->assertAccepted();

        $run = $quiz->generationRuns()->where('uuid', $response->json('data.uuid'))->firstOrFail();

        $this->assertSame('completed', $run->refresh()->status->value);
        $this->assertSame(40, $run->generated_count);
        $this->assertSame(40, $quiz->questions()->count());
    }
}
