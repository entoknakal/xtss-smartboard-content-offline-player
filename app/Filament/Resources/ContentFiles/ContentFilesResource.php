<?php

namespace App\Filament\Resources\ContentFiles;

use App\Filament\Resources\ContentFiles\Pages\CreateContentFiles;
use App\Filament\Resources\ContentFiles\Pages\EditContentFiles;
use App\Filament\Resources\ContentFiles\Pages\ListContentFiles;
use App\Filament\Resources\ContentFiles\Pages\ViewContentFiles;
use App\Filament\Resources\ContentFiles\Schemas\ContentFilesForm;
use App\Filament\Resources\ContentFiles\Schemas\ContentFilesInfolist;
use App\Filament\Resources\ContentFiles\Tables\ContentFilesTable;
use App\Models\ContentFiles;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContentFilesResource extends Resource
{
    protected static ?string $model = ContentFiles::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::ArrowDownOnSquare;

    protected static ?string $recordTitleAttribute = 'user_id';

    protected static ?int $navigationSort = 3;

    protected static ?string $pluralModelLabel = 'Data';

    public static function form(Schema $schema): Schema
    {
        return ContentFilesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContentFilesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentFilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentFiles::route('/'),
            'create' => CreateContentFiles::route('/create'),
            'view' => ViewContentFiles::route('/{record}'),
            'edit' => EditContentFiles::route('/{record}/edit'),
        ];
    }
}
