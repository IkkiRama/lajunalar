<?php

namespace App\Filament\Resources\QuizQuestions\Schemas;

use App\QuizType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QuizQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('quiz_id')
                    ->label('Paket')
                    ->relationship('quiz', 'title')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('position')
                    ->label('Nomor urut')
                    ->required()
                    ->integer()
                    ->minValue(1),
                Select::make('type')
                    ->label('Jenis soal')
                    ->options(QuizType::class)
                    ->required(),
                TextInput::make('question_text')
                    ->label('Susunan soal')
                    ->helperText('Gunakan susunan angka atau huruf dengan spasi agar mudah dibaca.')
                    ->required(),
                TextInput::make('correct_answer')
                    ->label('Jawaban benar')
                    ->maxLength(1)
                    ->required(),
                Textarea::make('explanation_correct')
                    ->label('Pembahasan')
                    ->columnSpanFull(),
            ]);
    }
}
