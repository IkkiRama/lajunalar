<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $user->quizStats()
            ->with('quiz:id,uuid,title,slug,type')
            ->latest('last_attempt_at')
            ->paginate(8);
        $totalAttempts = (int) $user->quizStats()->sum('attempts_count');
        $totalScore = (int) $user->quizStats()->sum('total_score');

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'role' => $user->role,
            ],
            'summary' => [
                'packages_completed' => $user->quizStats()->count(),
                'total_attempts' => $totalAttempts,
                'average_score' => $totalAttempts > 0 ? round($totalScore / $totalAttempts, 2) : 0,
            ],
            'history' => $stats->through(fn ($stat): array => [
                'quiz' => [
                    'uuid' => $stat->quiz->uuid,
                    'title' => $stat->quiz->title,
                    'slug' => $stat->quiz->slug,
                    'type' => $stat->quiz->type->value,
                ],
                'attempts_count' => $stat->attempts_count,
                'best_score' => $stat->best_score,
                'average_score' => round($stat->total_score / max(1, $stat->attempts_count), 2),
                'last_attempt_at' => $stat->last_attempt_at?->toIso8601String(),
            ]),
        ]);
    }
}
