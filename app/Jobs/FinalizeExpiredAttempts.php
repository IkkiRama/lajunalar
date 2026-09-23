<?php

namespace App\Jobs;

use App\Actions\FinalizeQuizAttempt;
use App\AttemptStatus;
use App\Models\QuizAttempt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FinalizeExpiredAttempts implements ShouldQueue
{
    use Queueable;

    public function __construct() {}

    public function handle(FinalizeQuizAttempt $finalize): void
    {
        QuizAttempt::query()
            ->where('status', AttemptStatus::InProgress)
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($attempts) use ($finalize): void {
                foreach ($attempts as $attempt) {
                    $finalize->handle($attempt, true);
                }
            });
    }
}
