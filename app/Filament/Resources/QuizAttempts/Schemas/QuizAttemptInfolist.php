<?php

namespace App\Filament\Resources\QuizAttempts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuizAttemptInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('quiz.title')
                    ->label('Quiz'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('question_order')
                    ->columnSpanFull(),
                TextEntry::make('next_position')
                    ->numeric(),
                TextEntry::make('score')
                    ->numeric(),
                TextEntry::make('answered_count')
                    ->numeric(),
                TextEntry::make('correct_count')
                    ->numeric(),
                TextEntry::make('wrong_count')
                    ->numeric(),
                TextEntry::make('unanswered_count')
                    ->numeric(),
                TextEntry::make('accuracy')
                    ->numeric(),
                TextEntry::make('average_response_ms')
                    ->numeric(),
                TextEntry::make('started_at')
                    ->dateTime(),
                TextEntry::make('expires_at')
                    ->dateTime(),
                TextEntry::make('finished_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_synced_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
