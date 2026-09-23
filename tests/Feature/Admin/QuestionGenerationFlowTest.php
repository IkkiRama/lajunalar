<?php

namespace Tests\Feature\Admin;

use App\Actions\QueueQuestionGeneration;
use App\GenerationStatus;
use App\Jobs\GenerateQuizQuestions;
use App\Models\Quiz;
use App\Models\User;
use App\QuizType;
use App\Services\QuestionGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class QuestionGenerationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_queue_a_mixed_generation_run(): void
    {
        Bus::fake();
        $admin = User::factory()->administrator()->create();
        $quiz = Quiz::factory()->create(['type' => QuizType::Mixed, 'question_count' => 500]);

        $run = app(QueueQuestionGeneration::class)->handle($quiz, 500, 225, $admin->id);

        $this->assertSame(GenerationStatus::Queued, $run->status);
        $this->assertSame(500, $run->requested_count);
        $this->assertSame(225, $run->number_count);
        $this->assertSame(275, $run->letter_count);
        Bus::assertDispatchedAfterResponse(GenerateQuizQuestions::class, fn (GenerateQuizQuestions $job): bool => $job->generationRunId === $run->id);
    }

    public function test_same_quiz_cannot_have_two_active_generation_runs(): void
    {
        Bus::fake();
        $admin = User::factory()->administrator()->create();
        $quiz = Quiz::factory()->create();
        $queue = app(QueueQuestionGeneration::class);
        $queue->handle($quiz, 500, 250, $admin->id);

        $this->expectException(ValidationException::class);
        $queue->handle($quiz, 500, 250, $admin->id);
    }

    public function test_progress_and_success_pages_render_for_admin(): void
    {
        $admin = User::factory()->administrator()->create();
        $quiz = Quiz::factory()->create(['title' => 'Paket Uji Generator']);
        $run = $quiz->generationRuns()->create([
            'created_by' => $admin->id,
            'requested_count' => 500,
            'generated_count' => 125,
            'number_count' => 250,
            'letter_count' => 250,
            'status' => GenerationStatus::Generating,
        ]);

        $this->actingAs($admin)
            ->get("/admin/question-generation-runs/{$run->uuid}/progress")
            ->assertOk()
            ->assertSee('125')
            ->assertSee('500')
            ->assertSee('Paket Uji Generator');

        $run->update([
            'status' => GenerationStatus::Completed,
            'generated_count' => 500,
            'finished_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get("/admin/question-generation-runs/{$run->uuid}/success")
            ->assertOk()
            ->assertSee('500 soal siap digunakan')
            ->assertSee('Coba kuis')
            ->assertSee('Kelola paket')
            ->assertSee('Generate lagi');
    }

    public function test_generation_job_creates_and_validates_the_requested_questions(): void
    {
        $admin = User::factory()->administrator()->create();
        $quiz = Quiz::factory()->create(['type' => QuizType::Mixed, 'question_count' => 40]);
        $run = $quiz->generationRuns()->create([
            'created_by' => $admin->id,
            'requested_count' => 40,
            'number_count' => 15,
            'letter_count' => 25,
            'status' => GenerationStatus::Queued,
        ]);

        (new GenerateQuizQuestions($run->id))->handle(app(QuestionGenerator::class));

        $run->refresh();
        $this->assertSame(GenerationStatus::Completed, $run->status);
        $this->assertSame(40, $run->generated_count);
        $this->assertSame(40, $quiz->questions()->count());
        $this->assertSame(15, $quiz->questions()->where('type', QuizType::Number)->count());
        $this->assertSame(25, $quiz->questions()->where('type', QuizType::Letter)->count());
    }
}
