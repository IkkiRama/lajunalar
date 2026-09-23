<?php

namespace App\Filament\Resources\QuestionGenerationRuns\Pages;

use App\Filament\Resources\QuestionGenerationRuns\QuestionGenerationRunResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditQuestionGenerationRun extends EditRecord
{
    protected static string $resource = QuestionGenerationRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
