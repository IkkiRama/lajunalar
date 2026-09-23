<?php

namespace App\Filament\Resources\QuestionReports;

use App\Filament\Resources\QuestionReports\Pages\EditQuestionReport;
use App\Filament\Resources\QuestionReports\Pages\ListQuestionReports;
use App\Filament\Resources\QuestionReports\Pages\ViewQuestionReport;
use App\Filament\Resources\QuestionReports\Schemas\QuestionReportForm;
use App\Filament\Resources\QuestionReports\Schemas\QuestionReportInfolist;
use App\Filament\Resources\QuestionReports\Tables\QuestionReportsTable;
use App\Models\QuestionReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class QuestionReportResource extends Resource
{
    protected static ?string $model = QuestionReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFlag;

    protected static ?string $navigationLabel = 'Laporan soal';

    public static function form(Schema $schema): Schema
    {
        return QuestionReportForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return QuestionReportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return QuestionReportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListQuestionReports::route('/'),
            'view' => ViewQuestionReport::route('/{record}'),
            'edit' => EditQuestionReport::route('/{record}/edit'),
        ];
    }
}
