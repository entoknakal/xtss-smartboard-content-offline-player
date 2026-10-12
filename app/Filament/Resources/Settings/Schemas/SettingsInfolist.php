<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SettingsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('uuid')
                    ->label('Value')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
