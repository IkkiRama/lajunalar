<?php

namespace App\Filament\Resources\QuizQuestions\Schemas;

use App\Models\QuizQuestion;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuizQuestionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('quiz.title')
                    ->label('Quiz'),
                TextEntry::make('position')
                    ->numeric(),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('question_text'),
                TextEntry::make('correct_answer'),
                TextEntry::make('explanation_correct')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('signature'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (QuizQuestion $record): bool => $record->trashed()),
            ]);
    }
}
