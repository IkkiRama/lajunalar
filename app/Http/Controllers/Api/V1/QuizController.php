<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuizResource;
use App\Models\Quiz;
use App\QuizStatus;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class QuizController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $quizzes = Quiz::query()
            ->where('status', QuizStatus::Published)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->withCount('questions')
            ->latest('published_at')
            ->paginate(12);

        return QuizResource::collection($quizzes);
    }

    public function show(Quiz $quiz): QuizResource
    {
        abort_unless($quiz->isAvailable(), 404);

        $quiz->loadCount('questions');
        $quiz->setRelation('attempts', $quiz->attempts()
            ->whereBelongsTo(request()->user())
            ->latest('started_at')
            ->limit(20)
            ->get());

        return new QuizResource($quiz);
    }
}
