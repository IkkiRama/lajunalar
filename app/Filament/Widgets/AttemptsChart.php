<?php

namespace App\Filament\Widgets;

use App\Models\QuizAttempt;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class AttemptsChart extends ChartWidget
{
    protected ?string $heading = 'Aktivitas 7 hari terakhir';

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn (int $daysAgo): Carbon => today()->subDays($daysAgo));
        $counts = QuizAttempt::query()
            ->where('created_at', '>=', $days->first()->startOfDay())
            ->selectRaw('DATE(created_at) as attempt_date, COUNT(*) as aggregate')
            ->groupBy('attempt_date')
            ->pluck('aggregate', 'attempt_date');

        return [
            'datasets' => [[
                'label' => 'Pengerjaan',
                'data' => $days->map(fn (Carbon $day): int => (int) ($counts[$day->toDateString()] ?? 0))->all(),
                'borderColor' => '#4f46e5',
                'backgroundColor' => 'rgba(79, 70, 229, .12)',
                'fill' => true,
                'tension' => .35,
            ]],
            'labels' => $days->map(fn (Carbon $day): string => $day->translatedFormat('D, d M'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
