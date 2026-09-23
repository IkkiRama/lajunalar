<?php

namespace App\Filament\Resources\QuestionGenerationRuns\Schemas;

use App\GenerationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QuestionGenerationRunForm
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
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('requested_count')
                    ->required()
                    ->numeric(),
                TextInput::make('generated_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('number_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('letter_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(GenerationStatus::class)
                    ->default('queued')
                    ->required(),
                Textarea::make('configuration')
                    ->columnSpanFull(),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('finished_at'),
            ]);
    }
}
