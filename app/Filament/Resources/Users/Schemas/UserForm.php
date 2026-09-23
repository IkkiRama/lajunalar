<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Nama')->disabled(),
            TextInput::make('email')->label('Email Google')->disabled(),
            Select::make('role')
                ->label('Peran')
                ->options(['user' => 'Pengguna', 'admin' => 'Administrator'])
                ->required(),
        ]);
    }
}
