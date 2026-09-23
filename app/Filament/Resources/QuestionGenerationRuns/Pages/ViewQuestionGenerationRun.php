<?php

namespace App\Filament\Resources\QuestionGenerationRuns\Pages;

use App\Filament\Resources\QuestionGenerationRuns\QuestionGenerationRunResource;
use Filament\Resources\Pages\ViewRecord;

class ViewQuestionGenerationRun extends ViewRecord
{
    protected static string $resource = QuestionGenerationRunResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
