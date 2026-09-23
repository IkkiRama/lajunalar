<?php

namespace App\Filament\Resources\QuestionReports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuestionReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('uuid')
                    ->label('UUID'),
                TextEntry::make('quiz_question_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('quiz_attempt_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('reason')
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('admin_note')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('resolved_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('resolved_at')
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
