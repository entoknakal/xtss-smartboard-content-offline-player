<?php

namespace App\Filament\Resources\ContentFiles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ContentFilesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user.name')
                    ->label('Nama Guru')
                    ->required()
                    ->numeric(),
                // Textarea::make('file_path')
                //     ->label('Nama file')
                //     ->required()
                //     ->columnSpanFull(),
                // Textarea::make('data_json')
                //     ->required()
                //     ->columnSpanFull(),
                TextInput::make('file_size_bytes')
                    ->required()
                    ->numeric(),
            ]);
    }
}
