<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                    ->saved(fn(?string $state): bool => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->belowContent(fn (string $operation) => $operation === 'edit' ? 'Isi kembali bila ingin mengubah password' : null),
                Select::make('role')
                    ->options(
                        function () {
                            return  ['admin' => 'Admin', 'guru' => 'Guru'];
                        }
                    )
                    ->required(
                        function () {
                            $superadmin = 'superadmin@localhost.com';
                            if (auth()->user()->email === $superadmin) {
                                return false;
                            }

                            return true;
                        }
                    ),
            ]);
    }
}
