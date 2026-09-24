<?php

namespace Tests\Feature\Api;

use App\AttemptStatus;
use App\GenerationStatus;
use App\Jobs\GenerateQuizQuestions;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\User;
use App\QuizStatus;
use App\QuizType;
use App\Services\QuestionGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AttemptWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_the_quiz_api(): void
    {
        $this->getJson('/api/v1/quizzes')->assertUnauthorized();
    }

    public function test_user_can_start_answer_in_batches_and_finish_an_attempt(): void
    {
        $user = User::factory()->create();
        [$quiz, $questions] = $this->numericQuiz(3);

        $start = $this->actingAs($user)->postJson("/api/v1/quizzes/{$quiz->uuid}/attempts")
            ->assertCreated()
            ->assertJsonMissing(['correct_answer' => '9']);
        $attemptUuid = $start->json('data.uuid');
        $presented = collect($start->json('data.questions'));

        $payload = $presented->take(2)->map(function (array $item) use ($questions): array {
            $question = $questions->firstWhere('uuid', $item['uuid']);

            return [
                'position' => $item['position'],
                'question_uuid' => $item['uuid'],
                'answer' => $question->correct_answer,
                'response_ms' => 250,
            ];
        })->all();

        $this->postJson("/api/v1/attempts/{$attemptUuid}/answers/batch", ['answers' => $payload])
            ->assertOk()
            ->assertJsonPath('data.next_position', 3);

        $this->postJson("/api/v1/attempts/{$attemptUuid}/finish")->assertOk();
        $this->assertDatabaseHas('quiz_attempts', [
            'uuid' => $attemptUuid,
            'score' => 2,
            'answered_count' => 2,
            'unanswered_count' => 1,
        ]);
        $this->assertDatabaseHas('user_quiz_stats', ['user_id' => $user->id, 'quiz_id' => $quiz->id, 'attempts_count' => 1]);

        $this->getJson("/api/v1/attempts/{$attemptUuid}/result")
            ->assertOk()
            ->assertJsonCount(3, 'data.answers')
            ->assertJsonPath('data.performance_by_type.number.answered', 2)
            ->assertJsonPath('data.performance_by_type.number.correct', 2)
            ->assertJsonPath('data.performance_by_type.letter.answered', 0)
            ->assertJsonPath('data.is_personal_best', true)
            ->assertJsonPath('data.answers.2.answer_text', null);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('history.data.0.latest_attempt.uuid', $attemptUuid)
            ->assertJsonPath('history.data.0.latest_attempt.status', 'submitted')
            ->assertJsonPath('performance_by_type.number.correct', 2)
            ->assertJsonPath('performance_by_type.number.accuracy', 100)
            ->assertJsonPath('performance_by_type.letter.answered', 0);
    }

    public function test_opening_quiz_detail_finalizes_an_expired_attempt(): void
    {
        $user = User::factory()->create();
        [$quiz] = $this->numericQuiz(1);
        $attemptUuid = $this->actingAs($user)
            ->postJson("/api/v1/quizzes/{$quiz->uuid}/attempts")
            ->assertCreated()
            ->json('data.uuid');

        QuizAttempt::query()
            ->where('uuid', $attemptUuid)
            ->update(['expires_at' => now()->subSecond()]);

        $this->getJson("/api/v1/quizzes/{$quiz->uuid}")
            ->assertOk()
            ->assertJsonPath('data.attempts.0.uuid', $attemptUuid)
            ->assertJsonPath('data.attempts.0.status', 'expired');

        $this->assertDatabaseHas('quiz_attempts', [
            'uuid' => $attemptUuid,
            'status' => AttemptStatus::Expired->value,
        ]);
        $this->getJson("/api/v1/attempts/{$attemptUuid}/result")->assertOk();
    }

    public function test_user_can_view_a_closed_quiz_detail_when_they_have_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        [$quiz] = $this->numericQuiz(1);
        $attemptUuid = $this->actingAs($user)
            ->postJson("/api/v1/quizzes/{$quiz->uuid}/attempts")
            ->assertCreated()
            ->json('data.uuid');

        $this->postJson("/api/v1/attempts/{$attemptUuid}/finish")->assertOk();
        $quiz->update(['ends_at' => now()->subMinute()]);

        $this->getJson("/api/v1/quizzes/{$quiz->uuid}")
            ->assertOk()
            ->assertJsonPath('data.can_start', false)
            ->assertJsonPath('data.attempts.0.uuid', $attemptUuid);

        $this->actingAs($otherUser)
            ->getJson("/api/v1/quizzes/{$quiz->uuid}")
            ->assertNotFound();
    }

    public function test_finished_attempt_result_survives_a_soft_deleted_quiz(): void
    {
        $user = User::factory()->create();
        [$quiz] = $this->numericQuiz(1);
        $attemptUuid = $this->actingAs($user)
            ->postJson("/api/v1/quizzes/{$quiz->uuid}/attempts")
            ->assertCreated()
            ->json('data.uuid');

        $this->postJson("/api/v1/attempts/{$attemptUuid}/finish")->assertOk();
        $quiz->delete();

        $this->getJson("/api/v1/attempts/{$attemptUuid}/result")
            ->assertOk()
            ->assertJsonPath('data.quiz.uuid', $quiz->uuid)
            ->assertJsonPath('data.quiz.can_start', false)
            ->assertJsonPath('data.quiz.can_view_detail', false);
    }

    public function test_answer_must_match_the_question_type(): void
    {
        $user = User::factory()->create();
        [$quiz] = $this->numericQuiz(1);
        $start = $this->actingAs($user)->postJson("/api/v1/quizzes/{$quiz->uuid}/attempts");

        $this->postJson('/api/v1/attempts/'.$start->json('data.uuid').'/answers/batch', [
            'answers' => [[
                'position' => 1,
                'question_uuid' => $start->json('data.questions.0.uuid'),
                'answer' => 'A',
                'response_ms' => 100,
            ]],
        ])->assertUnprocessable();
    }

    public function test_user_cannot_open_another_users_attempt(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        [$quiz] = $this->numericQuiz(1);
        $attempt = $this->actingAs($owner)->postJson("/api/v1/quizzes/{$quiz->uuid}/attempts")->json('data.uuid');

        $this->actingAs($intruder)->getJson("/api/v1/attempts/{$attempt}")->assertNotFound();
    }

    public function test_regenerating_a_quiz_does_not_change_a_finished_attempt_result(): void
    {
        $user = User::factory()->create();
        [$quiz, $questions] = $this->numericQuiz(2);
        $start = $this->actingAs($user)->postJson("/api/v1/quizzes/{$quiz->uuid}/attempts");
        $attemptUuid = $start->json('data.uuid');
        $presented = $start->json('data.questions.0');
        $oldQuestion = $questions->firstWhere('uuid', $presented['uuid']);

        $this->postJson("/api/v1/attempts/{$attemptUuid}/answers/batch", ['answers' => [[
            'position' => 1,
            'question_uuid' => $oldQuestion->uuid,
            'answer' => $oldQuestion->correct_answer,
            'response_ms' => 320,
        ]]])->assertOk();
        $this->postJson("/api/v1/attempts/{$attemptUuid}/finish")->assertOk();

        $this->regenerate($quiz, 2);

        $this->getJson("/api/v1/attempts/{$attemptUuid}/result")
            ->assertOk()
            ->assertJsonPath('data.score', 1)
            ->assertJsonPath('data.correct_count', 1)
            ->assertJsonPath('data.answers.0.question_uuid', $oldQuestion->uuid)
            ->assertJsonPath('data.answers.0.question_text', $oldQuestion->question_text)
            ->assertJsonPath('data.answers.0.correct_answer', $oldQuestion->correct_answer)
            ->assertJsonPath('data.answers.0.is_correct', true);
    }

    public function test_an_in_progress_attempt_keeps_its_original_questions_after_regeneration(): void
    {
        $user = User::factory()->create();
        [$quiz, $questions] = $this->numericQuiz(1);
        $start = $this->actingAs($user)->postJson("/api/v1/quizzes/{$quiz->uuid}/attempts");
        $attemptUuid = $start->json('data.uuid');
        $oldQuestion = $questions->first();

        $this->regenerate($quiz, 1);

        $this->getJson("/api/v1/attempts/{$attemptUuid}")
            ->assertOk()
            ->assertJsonPath('data.questions.0.uuid', $oldQuestion->uuid)
            ->assertJsonPath('data.questions.0.question_text', $oldQuestion->question_text);

        $this->postJson("/api/v1/attempts/{$attemptUuid}/answers/batch", ['answers' => [[
            'position' => 1,
            'question_uuid' => $oldQuestion->uuid,
            'answer' => $oldQuestion->correct_answer,
            'response_ms' => 210,
        ]]])->assertOk();
        $this->postJson("/api/v1/attempts/{$attemptUuid}/finish")->assertOk();
        $this->assertDatabaseHas('quiz_attempts', ['uuid' => $attemptUuid, 'score' => 1, 'correct_count' => 1]);
    }

    /** @return array{Quiz, Collection<int, QuizQuestion>} */
    private function numericQuiz(int $count): array
    {
        $quiz = Quiz::factory()->create([
            'type' => QuizType::Number,
            'status' => QuizStatus::Published,
            'question_count' => $count,
            'duration_seconds' => 300,
        ]);
        $questions = collect(range(1, $count))->map(fn (int $position): QuizQuestion => QuizQuestion::factory()->create([
            'quiz_id' => $quiz->id,
            'position' => $position,
            'question_text' => implode(' ', array_values(array_diff(range(0, 9), [$position % 10]))),
            'correct_answer' => (string) ($position % 10),
        ]));

        return [$quiz, $questions];
    }

    private function regenerate(Quiz $quiz, int $count): void
    {
        $run = $quiz->generationRuns()->create([
            'requested_count' => $count,
            'number_count' => $count,
            'letter_count' => 0,
            'status' => GenerationStatus::Queued,
        ]);

        (new GenerateQuizQuestions($run->id))->handle(app(QuestionGenerator::class));
    }
}
