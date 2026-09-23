<?php

namespace App;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AttemptStatus: string implements HasColor, HasLabel
{
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Expired = 'expired';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::InProgress => 'Sedang dikerjakan',
            self::Submitted => 'Selesai',
            self::Expired => 'Waktu habis',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::InProgress => 'warning',
            self::Submitted => 'success',
            self::Expired => 'danger',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->getLabel()])
            ->all();
    }
}
