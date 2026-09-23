<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email Google')->searchable(),
                TextColumn::make('role')->label('Peran')->badge()
                    ->color(fn (string $state): string => $state === 'admin' ? 'primary' : 'gray'),
                TextColumn::make('last_login_at')->label('Login terakhir')->since()->placeholder('Belum pernah')->sortable(),
                TextColumn::make('created_at')->label('Terdaftar')->dateTime('d M Y, H:i')->sortable(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->defaultSort('created_at', 'desc');
    }
}
