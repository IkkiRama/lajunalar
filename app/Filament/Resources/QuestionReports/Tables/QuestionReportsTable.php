<?php

namespace App\Filament\Resources\QuestionReports\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question.quiz.title')->label('Paket')->searchable(),
                TextColumn::make('question.position')->label('No.')->numeric()->sortable(),
                TextColumn::make('user.name')->label('Pelapor')->searchable(),
                TextColumn::make('reason')->label('Alasan')->limit(55)->wrap(),
                TextColumn::make('status')->badge()->color(fn (string $state): string => match ($state) {
                    'resolved' => 'success',
                    'reviewing' => 'warning',
                    default => 'danger',
                }),
                TextColumn::make('resolver.name')->label('Diselesaikan oleh')->placeholder('-'),
                TextColumn::make('created_at')->label('Dilaporkan')->since()->sortable(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }
}
