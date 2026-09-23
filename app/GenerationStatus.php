<?php

namespace App;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GenerationStatus: string implements HasColor, HasLabel
{
    case Queued = 'queued';
    case Generating = 'generating';
    case Validating = 'validating';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'Menunggu antrean',
            self::Generating => 'Membuat soal',
            self::Validating => 'Memvalidasi',
            self::Completed => 'Selesai',
            self::Failed => 'Gagal',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Queued => 'gray',
            self::Generating => 'warning',
            self::Validating => 'info',
            self::Completed => 'success',
            self::Failed => 'danger',
        };
    }
}
