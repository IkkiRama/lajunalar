<?php

namespace App\Filament\Resources\QuestionGenerationRuns\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuestionGenerationRunInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('quiz.title')
                    ->label('Quiz'),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('requested_count')
                    ->numeric(),
                TextEntry::make('generated_count')
                    ->numeric(),
                TextEntry::make('number_count')
                    ->numeric(),
                TextEntry::make('letter_count')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('configuration')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('error_message')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('started_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('finished_at')
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
