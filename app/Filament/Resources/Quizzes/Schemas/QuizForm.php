<?php

namespace App\Filament\Resources\Quizzes\Schemas;

use App\QuizStatus;
use App\QuizType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class QuizForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas paket')
                    ->description('Informasi yang dilihat pengguna sebelum memulai latihan.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Nama paket')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Variasi soal')
                            ->options(QuizType::class)
                            ->default(QuizType::Mixed->value)
                            ->required(),
                        Select::make('status')
                            ->options(QuizStatus::class)
                            ->default(QuizStatus::Draft->value)
                            ->required(),
                    ])->columns(2),
                Section::make('Aturan pengerjaan')
                    ->schema([
                        TextInput::make('question_count')
                            ->label('Jumlah soal')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(2000)
                            ->default(500),
                        TextInput::make('duration_seconds')
                            ->label('Durasi (detik)')
                            ->helperText('300 detik = 5 menit.')
                            ->required()
                            ->integer()
                            ->minValue(10)
                            ->maxValue(10800)
                            ->default(300),
                        TextInput::make('passing_score')
                            ->label('Nilai lulus (opsional)')
                            ->integer()
                            ->minValue(0),
                        DateTimePicker::make('starts_at')->label('Mulai tersedia'),
                        DateTimePicker::make('ends_at')->label('Berakhir pada'),
                    ])->columns(2),
            ]);
    }
}
