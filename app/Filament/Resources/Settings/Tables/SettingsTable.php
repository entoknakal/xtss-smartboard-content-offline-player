<?php

namespace App\Filament\Resources\Settings\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                function (Builder $query) {
                    $list = [
                        'license_id',
                        'npsn',
                        'nama_sekolah',
                        'status',
                        'alamat',
                        'lintang',
                        'bujur',
                        'propinsi',
                        'kota',
                        'kecamatan',
                    ];
                    return $query->whereNotIn('name', $list);
                    
                }
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('uuid')
                    ->label('Value')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
