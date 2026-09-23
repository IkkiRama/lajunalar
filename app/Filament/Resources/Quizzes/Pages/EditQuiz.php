<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Actions\QueueQuestionGeneration;
use App\Filament\Resources\QuestionGenerationRuns\QuestionGenerationRunResource;
use App\Filament\Resources\Quizzes\QuizResource;
use App\Models\Quiz;
use App\QuizStatus;
use App\QuizType;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuiz extends EditRecord
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateQuestions')
                ->label('Generate soal')
                ->icon('heroicon-o-sparkles')
                ->color('warning')
                ->schema([
                    TextInput::make('count')
                        ->label('Jumlah soal')
                        ->integer()
                        ->minValue(1)
                        ->maxValue(2000)
                        ->default(fn (Quiz $record): int => $record->question_count)
                        ->required(),
                    TextInput::make('number_count')
                        ->label('Jumlah soal angka')
                        ->helperText('Pada paket huruf nilainya diabaikan. Pada paket campuran, sisanya menjadi soal huruf.')
                        ->integer()
                        ->minValue(0)
                        ->default(fn (Quiz $record): int => $record->type === QuizType::Letter ? 0 : intdiv($record->question_count, 2)),
                ])
                ->requiresConfirmation()
                ->modalDescription('Bank soal draft saat ini akan diganti. Riwayat attempt tetap aman karena jawaban menyimpan snapshot soal.')
                ->action(function (array $data, Quiz $record) {
                    $run = app(QueueQuestionGeneration::class)->handle(
                        $record,
                        (int) $data['count'],
                        isset($data['number_count']) ? (int) $data['number_count'] : null,
                        (int) auth()->id(),
                    );
                    Notification::make()->title('Generator masuk antrean.')->success()->send();

                    return redirect(QuestionGenerationRunResource::getUrl('progress', ['record' => $run]));
                }),
            Action::make('publish')
                ->label('Publikasikan')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (Quiz $record): bool => $record->status !== QuizStatus::Published)
                ->requiresConfirmation()
                ->action(function (Quiz $record): void {
                    $questionCount = $record->questions()->count();

                    if ($questionCount < $record->question_count) {
                        Notification::make()
                            ->title("Soal belum cukup: {$questionCount}/{$record->question_count}")
                            ->danger()
                            ->send();

                        return;
                    }

                    $record->update(['status' => QuizStatus::Published, 'published_at' => now()]);
                    Notification::make()->title('Paket berhasil dipublikasikan.')->success()->send();
                }),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
