<?php

namespace App\Filament\Resources\QuestionReports\Pages;

use App\Filament\Resources\QuestionReports\QuestionReportResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionReport extends EditRecord
{
    protected static string $resource = QuestionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'resolved') {
            $data['resolved_by'] = auth()->id();
            $data['resolved_at'] = now();
        }

        return $data;
    }
}
