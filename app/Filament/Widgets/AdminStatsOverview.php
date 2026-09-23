<?php

namespace App\Filament\Widgets;

use App\AttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\QuizStatus;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total pengguna', User::query()->where('role', 'user')->count())
                ->description('Akun Google terdaftar')
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Paket aktif', Quiz::query()->where('status', QuizStatus::Published)->count())
                ->description('Tersedia untuk latihan')
                ->icon('heroicon-o-rectangle-stack')
                ->color('success'),
            Stat::make('Pengerjaan hari ini', QuizAttempt::query()->whereDate('created_at', today())->count())
                ->description('Semua paket')
                ->icon('heroicon-o-bolt')
                ->color('warning'),
            Stat::make('Sedang berlangsung', QuizAttempt::query()->where('status', AttemptStatus::InProgress)->count())
                ->description('Percobaan aktif')
                ->icon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}
