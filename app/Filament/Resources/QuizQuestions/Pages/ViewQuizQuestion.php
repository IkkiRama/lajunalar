<?php

namespace App\Filament\Resources\QuizQuestions\Pages;

use App\Filament\Resources\QuizQuestions\QuizQuestionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewQuizQuestion extends ViewRecord
{
    protected static string $resource = QuizQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
