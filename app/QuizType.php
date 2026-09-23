<?php

namespace App;

use Filament\Support\Contracts\HasLabel;

enum QuizType: string implements HasLabel
{
    case Number = 'number';
    case Letter = 'letter';
    case Mixed = 'mixed';

    public function label(): string
    {
        return match ($this) {
            self::Number => 'Angka hilang',
            self::Letter => 'Huruf ganda',
            self::Mixed => 'Campuran',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
