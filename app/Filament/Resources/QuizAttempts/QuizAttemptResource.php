<?php

namespace App\Filament\Resources\QuizAttempts;

use App\Filament\Resources\QuizAttempts\Pages\ListQuizAttempts;
use App\Filament\Resources\QuizAttempts\Pages\ViewQuizAttempt;
use App\Filament\Resources\QuizAttempts\Schemas\QuizAttemptForm;
use App\Filament\Resources\QuizAttempts\Schemas\QuizAttemptInfolist;
use App\Filament\Resources\QuizAttempts\Tables\QuizAttemptsTable;
use App\Models\QuizAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuizAttemptResource extends Resource
{
    protected static ?string $model = QuizAttempt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?string $navigationLabel = 'Pengerjaan';

    protected static ?string $modelLabel = 'pengerjaan';

    protected static ?string $pluralModelLabel = 'pengerjaan';

    public static function form(Schema $schema): Schema
    {
        return QuizAttemptForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuizAttemptInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuizAttemptsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuizAttempts::route('/'),
            'view' => ViewQuizAttempt::route('/{record}'),
        ];
    }
}
