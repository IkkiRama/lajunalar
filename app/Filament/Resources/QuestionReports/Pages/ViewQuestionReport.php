<?php

namespace App\Filament\Resources\QuestionReports\Pages;

use App\Filament\Resources\QuestionReports\QuestionReportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestionReport extends ViewRecord
{
    protected static string $resource = QuestionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
