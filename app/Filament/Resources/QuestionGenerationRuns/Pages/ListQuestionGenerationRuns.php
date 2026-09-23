<?php

namespace App\Filament\Resources\QuestionGenerationRuns\Pages;

use App\Actions\QueueQuestionGeneration;
use App\Filament\Resources\QuestionGenerationRuns\QuestionGenerationRunResource;
use App\Models\Quiz;
use App\QuizType;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ListQuestionGenerationRuns extends ListRecords
{
    protected static string $resource = QuestionGenerationRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateQuestions')
                ->label('Generate soal')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->modalHeading('Buat bank soal otomatis')
                ->modalDescription('Pilih paket tujuan dan komposisi soal. Sistem akan mengganti bank soal aktif paket tersebut tanpa menghapus riwayat pengerjaan lama.')
                ->modalSubmitActionLabel('Mulai generate')
                ->schema([
                    Select::make('quiz_id')
                        ->label('Pilih paket')
                        ->placeholder('Cari nama paket')
                        ->options(fn (): array => Quiz::query()->orderBy('title')->pluck('title', 'id')->all())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            $quiz = $state ? Quiz::query()->find($state) : null;

                            if ($quiz === null) {
                                return;
                            }

                            $set('count', $quiz->bank_size);
                            $set('number_count', match ($quiz->type) {
                                QuizType::Number => $quiz->bank_size,
                                QuizType::Letter => 0,
                                QuizType::Mixed => (int) round($quiz->bank_size * $quiz->number_percentage / 100),
                            });
                        })
                        ->required(),
                    TextInput::make('count')
                        ->label('Ukuran bank soal')
                        ->helperText('Minimal sama dengan soal per attempt; maksimum 5.000.')
                        ->integer()
                        ->minValue(function (Get $get): int {
                            $quizId = $get('quiz_id');

                            return $quizId ? (int) Quiz::query()->find($quizId)?->question_count : 1;
                        })
                        ->maxValue(5000)
                        ->default(1500)
                        ->required(),
                    TextInput::make('number_count')
                        ->label('Jumlah soal angka')
                        ->helperText('Sisa jumlah soal otomatis menjadi soal huruf.')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(fn (Get $get): int => max(1, (int) ($get('count') ?: 5000)))
                        ->default(750)
                        ->visible(function (Get $get): bool {
                            $quizId = $get('quiz_id');

                            return $quizId && Quiz::query()->find($quizId)?->type === QuizType::Mixed;
                        })
                        ->required(fn (Get $get): bool => filled($get('quiz_id'))),
                ])
                ->action(function (array $data): void {
                    $quiz = Quiz::query()->findOrFail($data['quiz_id']);
                    $run = app(QueueQuestionGeneration::class)->handle(
                        $quiz,
                        (int) $data['count'],
                        isset($data['number_count']) ? (int) $data['number_count'] : null,
                        (int) auth()->id(),
                    );

                    Notification::make()
                        ->title('Generator dimulai')
                        ->body("Menyiapkan {$run->requested_count} soal untuk {$quiz->title}.")
                        ->success()
                        ->send();

                    $this->redirect(
                        QuestionGenerationRunResource::getUrl('progress', ['record' => $run]),
                        navigate: true,
                    );
                }),
        ];
    }
}
