<?php

namespace App\Filament\Resources\QuizAttempts\Schemas;

use App\AttemptStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QuizAttemptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('uuid')
                    ->label('UUID')
                    ->required(),
                Select::make('quiz_id')
                    ->relationship('quiz', 'title')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('status')
                    ->options(AttemptStatus::class)
                    ->default('in_progress')
                    ->required(),
                Textarea::make('question_order')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('next_position')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('score')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('answered_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('correct_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('wrong_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('unanswered_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('accuracy')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('average_response_ms')
                    ->required()
                    ->numeric()
                    ->default(0),
                DateTimePicker::make('started_at')
                    ->required(),
                DateTimePicker::make('expires_at')
                    ->required(),
                DateTimePicker::make('finished_at'),
                DateTimePicker::make('last_synced_at'),
            ]);
    }
}
