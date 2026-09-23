<?php

namespace App\Filament\Resources\QuestionGenerationRuns\Tables;

use App\Filament\Resources\QuestionGenerationRuns\QuestionGenerationRunResource;
use App\GenerationStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionGenerationRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quiz.title')
                    ->label('Paket')
                    ->searchable(),
                TextColumn::make('creator.name')->label('Dijalankan oleh'),
                TextColumn::make('progress')
                    ->label('Progres')
                    ->state(fn ($record): string => "{$record->generated_count}/{$record->requested_count}")
                    ->description(fn ($record): string => $record->status->label()),
                TextColumn::make('number_count')
                    ->label('Angka')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('letter_count')
                    ->label('Huruf')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->url(fn ($record): string => QuestionGenerationRunResource::getUrl(
                        $record->status === GenerationStatus::Completed ? 'success' : 'progress',
                        ['record' => $record],
                    )),
            ])
            ->poll('1s');
    }
}
