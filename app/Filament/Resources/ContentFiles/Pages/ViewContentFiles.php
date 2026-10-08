<?php

namespace App\Filament\Resources\ContentFiles\Pages;

use App\Filament\Resources\ContentFiles\ContentFilesResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContentFiles extends ViewRecord
{
    protected static string $resource = ContentFilesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // EditAction::make(),
            Action::make('back')
                ->label('Kembali')
                ->color('gray') // Memberikan warna netral abu-abu khas tombol kembali
                ->icon('heroicon-m-arrow-left') // Menambahkan ikon panah ke kiri
                ->url(static::$resource::getUrl('index')), // Otomatis mengarah ke halaman daftar utama (/admin/content-files)
        ];
    }
}
