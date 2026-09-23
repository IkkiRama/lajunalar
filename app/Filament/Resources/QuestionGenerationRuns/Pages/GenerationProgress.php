<?php

namespace App\Filament\Resources\QuestionGenerationRuns\Pages;

use App\Filament\Resources\QuestionGenerationRuns\QuestionGenerationRunResource;
use App\GenerationStatus;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class GenerationProgress extends ViewRecord
{
    protected static string $resource = QuestionGenerationRunResource::class;

    protected string $view = 'filament.resources.question-generation-runs.pages.generation-progress';

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->record->load('quiz');
        $this->checkGenerationStatus();
    }

    public function checkGenerationStatus(): void
    {
        $this->record = $this->getRecord()->refresh()->load('quiz');

        if ($this->record->status === GenerationStatus::Completed) {
            $this->redirect(
                QuestionGenerationRunResource::getUrl('success', ['record' => $this->record]),
                navigate: true,
            );
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Generator soal sedang bekerja';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
