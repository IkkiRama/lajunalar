<?php

namespace App\Filament\Resources\QuestionGenerationRuns\Pages;

use App\Filament\Resources\QuestionGenerationRuns\QuestionGenerationRunResource;
use App\Filament\Resources\Quizzes\QuizResource;
use App\GenerationStatus;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class GenerationSuccess extends ViewRecord
{
    protected static string $resource = QuestionGenerationRunResource::class;

    protected string $view = 'filament.resources.question-generation-runs.pages.generation-success';

    public function mount(int|string $record): void
    {
        parent::mount($record);
        $this->record->load('quiz');

        if ($this->record->status !== GenerationStatus::Completed) {
            $this->redirect(
                QuestionGenerationRunResource::getUrl('progress', ['record' => $this->record]),
                navigate: true,
            );
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'Soal berhasil dibuat';
    }

    public function getTryQuizUrl(): string
    {
        return url('/paket/'.$this->getRecord()->quiz->uuid);
    }

    public function getEditQuizUrl(): string
    {
        return QuizResource::getUrl('edit', ['record' => $this->getRecord()->quiz]);
    }

    public function getGenerateAgainUrl(): string
    {
        return QuestionGenerationRunResource::getUrl('index').'?action=generateQuestions';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
