<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\Models\Quiz;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuizInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('slug'),
                TextEntry::make('title'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('question_count')
                    ->numeric(),
                TextEntry::make('duration_seconds')
                    ->numeric(),
                TextEntry::make('passing_score')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('generation_config')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('starts_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('ends_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Quiz $record): bool => $record->trashed()),
            ]);
    }
}
