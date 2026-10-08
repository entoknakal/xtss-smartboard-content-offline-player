<?php

namespace App\Filament\Resources\ContentFiles\Pages;

use App\Filament\Resources\ContentFiles\ContentFilesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditContentFiles extends EditRecord
{
    protected static string $resource = ContentFilesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
