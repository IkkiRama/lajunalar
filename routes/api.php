<?php

use App\Http\Controllers\Api\V1\AdminGenerationController;
use App\Http\Controllers\Api\V1\AdminQuizController;
use App\Http\Controllers\Api\V1\AttemptAnswerBatchController;
use App\Http\Controllers\Api\V1\AttemptResultController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\QuestionReportController;
use App\Http\Controllers\Api\V1\QuizAttemptController;
use App\Http\Controllers\Api\V1\QuizController;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::bind('quizQuestion', fn (string $value) => QuizQuestion::query()
    ->withTrashed()
    ->where('uuid', $value)
    ->firstOrFail());

Route::prefix('v1')->middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', fn (Request $request) => $request->user())->name('api.v1.me');
    Route::get('/dashboard', DashboardController::class)->name('api.v1.dashboard');
    Route::get('/quizzes', [QuizController::class, 'index'])->name('api.v1.quizzes.index');
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('api.v1.quizzes.show');
    Route::post('/quizzes/{quiz}/attempts', [QuizAttemptController::class, 'store'])
        ->middleware('throttle:attempt-start')
        ->name('api.v1.attempts.store');
    Route::get('/attempts/{attempt}', [QuizAttemptController::class, 'show'])->name('api.v1.attempts.show');
    Route::post('/attempts/{attempt}/answers/batch', AttemptAnswerBatchController::class)
        ->middleware('throttle:answer-batches')
        ->name('api.v1.attempts.answers.store');
    Route::post('/attempts/{attempt}/finish', [QuizAttemptController::class, 'finish'])
        ->name('api.v1.attempts.finish');
    Route::get('/attempts/{attempt}/result', AttemptResultController::class)
        ->name('api.v1.attempts.result');
    Route::post('/questions/{quizQuestion}/reports', QuestionReportController::class)
        ->middleware('throttle:10,1')
        ->name('api.v1.question-reports.store');

    Route::prefix('admin')->middleware('admin')->group(function (): void {
        Route::get('/quizzes', [AdminQuizController::class, 'index'])->name('api.v1.admin.quizzes.index');
        Route::post('/quizzes', [AdminQuizController::class, 'store'])->name('api.v1.admin.quizzes.store');
        Route::put('/quizzes/{quiz}', [AdminQuizController::class, 'update'])->name('api.v1.admin.quizzes.update');
        Route::delete('/quizzes/{quiz}', [AdminQuizController::class, 'destroy'])->name('api.v1.admin.quizzes.destroy');
        Route::post('/quizzes/{quiz}/generations', [AdminGenerationController::class, 'store'])->name('api.v1.admin.generations.store');
        Route::get('/generations', [AdminGenerationController::class, 'index'])->name('api.v1.admin.generations.index');
        Route::get('/generations/{generation}', [AdminGenerationController::class, 'show'])->name('api.v1.admin.generations.show');
        Route::post('/generations/{generation}/resume', [AdminGenerationController::class, 'resume'])->name('api.v1.admin.generations.resume');
    });
});
