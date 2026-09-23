<?php

namespace App\Filament\Resources\QuestionReports\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Laporan pengguna')->schema([
                TextInput::make('user.name')->label('Pengguna')->disabled(),
                TextInput::make('question.question_text')->label('Soal')->disabled(),
                Textarea::make('reason')->label('Alasan')->disabled()->columnSpanFull(),
            ]),
            Section::make('Tindak lanjut')->schema([
                Select::make('status')
                    ->options(['open' => 'Terbuka', 'reviewing' => 'Ditinjau', 'resolved' => 'Selesai'])
                    ->required(),
                Textarea::make('admin_note')->label('Catatan admin')->columnSpanFull(),
            ]),
        ]);
    }
}
