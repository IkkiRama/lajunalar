<?php

namespace App\Filament\Resources\QuizAttempts\Tables;

use App\AttemptStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuizAttemptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quiz.title')->label('Paket')->searchable()->sortable(),
                TextColumn::make('user.name')->label('Pengguna')->searchable()->sortable(),
                TextColumn::make('status')->label('Status')->badge()->sortable(),
                TextColumn::make('score')->label('Skor')->numeric()->sortable(),
                TextColumn::make('answered_count')->label('Dijawab')->numeric()->sortable(),
                TextColumn::make('accuracy')->label('Akurasi')->numeric()->suffix('%')->sortable(),
                TextColumn::make('average_response_ms')
                    ->label('Rata-rata respons')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1000, 2).' dtk' : '-'),
                TextColumn::make('started_at')->label('Mulai')->dateTime('d M Y, H:i')->sortable(),
                TextColumn::make('finished_at')->label('Selesai')->dateTime('d M Y, H:i')->placeholder('-')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(AttemptStatus::options()),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('started_at', 'desc');
    }
}
