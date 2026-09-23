<?php

namespace App\Http\Controllers\Api\V1;

use App\AttemptStatus;
use App\Http\Controllers\Controller;
use App\Models\QuizAttemptAnswer;
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
        $latestAttempts = $user->attempts()
            ->whereIn('quiz_id', $stats->getCollection()->pluck('quiz_id'))
            ->whereIn('status', [AttemptStatus::Submitted, AttemptStatus::Expired])
            ->latest('finished_at')
            ->get()
            ->unique('quiz_id')
            ->keyBy('quiz_id');
        $totalAttempts = (int) $user->quizStats()->sum('attempts_count');
        $totalScore = (int) $user->quizStats()->sum('total_score');
        $typeRows = QuizAttemptAnswer::query()
            ->whereHas('attempt', fn ($query) => $query
                ->where('user_id', $user->id)
                ->whereIn('status', [AttemptStatus::Submitted, AttemptStatus::Expired]))
            ->selectRaw('question_type, COUNT(*) as answered_count')
            ->selectRaw('SUM(CASE WHEN is_correct = 1 THEN 1 ELSE 0 END) as correct_count')
            ->selectRaw('COALESCE(AVG(response_ms), 0) as average_response_ms')
            ->groupBy('question_type')
            ->get()
            ->keyBy('question_type');
        $typePerformance = function (string $type) use ($typeRows): array {
            $row = $typeRows->get($type);
            $answered = (int) ($row?->answered_count ?? 0);
            $correct = (int) ($row?->correct_count ?? 0);

            return [
                'answered' => $answered,
                'correct' => $correct,
                'wrong' => $answered - $correct,
                'accuracy' => $answered > 0 ? round($correct / $answered * 100, 2) : 0,
                'average_response_ms' => (int) round((float) ($row?->average_response_ms ?? 0)),
            ];
        };

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
            'performance_by_type' => [
                'number' => $typePerformance('number'),
                'letter' => $typePerformance('letter'),
            ],
            'history' => $stats->through(function ($stat) use ($latestAttempts): array {
                $latestAttempt = $latestAttempts->get($stat->quiz_id);

                return [
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
                    'latest_attempt' => $latestAttempt ? [
                        'uuid' => $latestAttempt->uuid,
                        'score' => $latestAttempt->score,
                        'status' => $latestAttempt->status->value,
                        'finished_at' => $latestAttempt->finished_at?->toIso8601String(),
                    ] : null,
                ];
            }),
        ]);
    }
}
