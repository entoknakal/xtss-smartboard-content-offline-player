<?php

namespace App\Filament\Resources\ContentFiles\Pages;

use App\Filament\Resources\ContentFiles\ContentFilesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentFiles extends ListRecords
{
    protected static string $resource = ContentFilesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
