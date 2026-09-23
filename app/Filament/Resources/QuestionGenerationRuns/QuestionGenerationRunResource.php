<?php

namespace App\Filament\Resources\QuestionGenerationRuns;

use App\Filament\Resources\QuestionGenerationRuns\Pages\GenerationProgress;
use App\Filament\Resources\QuestionGenerationRuns\Pages\GenerationSuccess;
use App\Filament\Resources\QuestionGenerationRuns\Pages\ListQuestionGenerationRuns;
use App\Filament\Resources\QuestionGenerationRuns\Pages\ViewQuestionGenerationRun;
use App\Filament\Resources\QuestionGenerationRuns\Schemas\QuestionGenerationRunForm;
use App\Filament\Resources\QuestionGenerationRuns\Schemas\QuestionGenerationRunInfolist;
use App\Filament\Resources\QuestionGenerationRuns\Tables\QuestionGenerationRunsTable;
use App\Models\QuestionGenerationRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuestionGenerationRunResource extends Resource
{
    protected static ?string $model = QuestionGenerationRun::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Generator soal';

    protected static ?string $modelLabel = 'proses generator';

    protected static ?string $pluralModelLabel = 'riwayat generator';

    public static function form(Schema $schema): Schema
    {
        return QuestionGenerationRunForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuestionGenerationRunInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionGenerationRunsTable::configure($table);
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
            'index' => ListQuestionGenerationRuns::route('/'),
            'progress' => GenerationProgress::route('/{record}/progress'),
            'success' => GenerationSuccess::route('/{record}/success'),
            'view' => ViewQuestionGenerationRun::route('/{record}'),
        ];
    }
}
