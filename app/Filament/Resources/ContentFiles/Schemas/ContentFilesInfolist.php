<?php

namespace App\Filament\Resources\ContentFiles\Schemas;

use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\Width;
use Hugomyb\FilamentMediaAction\Actions\MediaAction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

class ContentFilesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. DATA POKOK FILE UTAMA
                Grid::make()
                    ->schema([
                        TextEntry::make('user.name'),
                        TextEntry::make('file_path'),
                        TextEntry::make('file_size_bytes')
                            ->formatStateUsing(fn($state) => Number::fileSize($state, precision: 2)),
                        TextEntry::make('updated_at')
                            ->label('Waktu Diperbarui')
                            ->dateTime('d F Y, H:i', timezone: 'Asia/Jakarta')
                            ->suffix(' WIB'),
                    ])->columnSpanFull(),
                Section::make()
                    ->schema([
                        // LOOP LEVEL 1: Kelas
                        RepeatableEntry::make('daftar_kelas')
                            ->hiddenLabel()
                            ->contained(false)
                            ->schema([
                                TextEntry::make('nama_kelas')
                                    ->hiddenLabel()
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large),

                                // LOOP LEVEL 2: Kumpulan Semester
                                RepeatableEntry::make('materi_ajar')
                                    ->hiddenLabel()
                                    ->contained(false)
                                    ->schema([
                                        TextEntry::make('nama_semester')
                                            ->hidden(),
                                        Section::make(fn(Get $get) => $get('nama_semester'))
                                            ->collapsible()
                                            ->schema([
                                                RepeatableEntry::make('daftar_mapel')
                                                    ->hiddenLabel()
                                                    ->contained(false)
                                                    ->schema([

                                                        // BUNGKUS DENGAN SECTION UTUH:
                                                        // Menggunakan Judul Statis menjamin RAM server Anda 100% Aman & Ringan!
                                                        Section::make(fn(Get $get) => $get('nama_mata_pelajaran'))
                                                            ->icon('heroicon-o-book-open')
                                                            ->collapsible()
                                                            ->schema([
                                                                TextEntry::make('nama_mata_pelajaran')
                                                                    ->hidden(),

                                                                // LOOP LEVEL 4: Detail Elemen di dalam Mata Pelajaran tersebut
                                                                RepeatableEntry::make('detail_elemen')
                                                                    ->hiddenLabel()
                                                                    ->contained(true)
                                                                    ->schema([
                                                                        Grid::make(2)
                                                                            ->schema([
                                                                                TextEntry::make('elemen_pembelajaran')
                                                                                    ->label('Elemen')
                                                                                    ->weight(FontWeight::SemiBold),

                                                                                TextEntry::make('author')
                                                                                    ->label('Penulis / Author')
                                                                                    ->icon('heroicon-m-user'),
                                                                            ]),

                                                                        TextEntry::make('deskripsi_elemen_pembelajaran')
                                                                            ->label('Deskripsi Elemen Pembelajaran')
                                                                            ->columnSpanFull(),

                                                                        // LOOP LEVEL 5: Kumpulan Bab & Tujuan Pembelajaran
                                                                        RepeatableEntry::make('materi')
                                                                            ->label('Detail Bab & Tujuan Pembelajaran')
                                                                            ->columnSpanFull()
                                                                            ->grid(['md' => 2])
                                                                            ->schema([
                                                                                Grid::make(1)
                                                                                    ->schema([
                                                                                        TextEntry::make('nama_bab')
                                                                                            ->label('Bab')
                                                                                            ->weight('bold'),

                                                                                        TextEntry::make('alokasi_waktu')
                                                                                            ->label('Alokasi Waktu')
                                                                                            ->suffix(' JP'),

                                                                                        TextEntry::make('tujuan_pembelajaran')
                                                                                            ->label('Tujuan Pembelajaran')
                                                                                            ->html()
                                                                                            ->prose(),
                                                                                    ]),
                                                                            ]),
                                                                        RepeatableEntry::make('konten_file_path')
                                                                            ->label('Berkas / File Materi')
                                                                            ->columnSpanFull()
                                                                            ->grid([
                                                                                'sm' => 2,
                                                                                'md' => 3,
                                                                            ])
                                                                            ->schema([
                                                                                TextEntry::make('path_lengkap')
                                                                                    ->label('Nama File')
                                                                                    ->formatStateUsing(fn($state) => basename($state))
                                                                                    ->visible(fn($state) => !str_ends_with($state, '.h5p') && !str_ends_with($state, '.html'))
                                                                                    ->hintActions([
                                                                                        MediaAction::make('preview')
                                                                                            ->label('Preview')
                                                                                            ->icon(function ($state) {
                                                                                                $extension = strtolower(pathinfo($state, PATHINFO_EXTENSION));
                                                                                                return match ($extension) {
                                                                                                    'mp4', 'avi', 'mov', 'webm', 'mkv', 'flv', 'wmv', '3gp', 'ogv', 'm4v' => 'heroicon-o-video-camera',
                                                                                                    'mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'wma' => 'heroicon-o-speaker-wave',
                                                                                                    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' => 'heroicon-o-document',
                                                                                                    'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp', 'tiff', 'ico' => 'heroicon-o-photo',
                                                                                                    default => 'heroicon-o-play-circle',
                                                                                                };
                                                                                            })
                                                                                            ->media(function ($state) {
                                                                                                return Storage::disk('local')->temporaryUrl('ztss_extracted/' . $state, now()->addMinutes(5));
                                                                                            })
                                                                                            ->disableDownload(false),
                                                                                    ]),
                                                                                TextEntry::make('path_lengkap')
                                                                                    ->label('HTML5')
                                                                                    ->badge()
                                                                                    ->color('success')
                                                                                    // Memastikan data terisi dan mengecek ekstensi .html secara aman
                                                                                    ->visible(fn($state) => filled($state) && str_ends_with((string) $state, '.html'))
                                                                                    // Hanya memunculkan nama filenya saja pada tampilan badge
                                                                                    ->formatStateUsing(fn($state) => basename($state))
                                                                                    ->hintActions([
                                                                                        Action::make('openHtml')
                                                                                            ->label('Preview')
                                                                                            ->icon('heroicon-o-newspaper')
                                                                                            ->modalHeading('HTML Viewer')
                                                                                            ->modalWidth(Width::Full)
                                                                                            ->modalContent(
                                                                                                function ($state) {
                                                                                                    $explode_state = explode('/', $state);

                                                                                                    // Ambil parameter untuk Route Viewer HTML5 Anda
                                                                                                    $uuid = $explode_state[0];
                                                                                                    $userId = $explode_state[1];
                                                                                                    $kelasId = $explode_state[2];

                                                                                                    return new \Illuminate\Support\HtmlString(
                                                                                                        "<iframe src='" . route('html.viewer', [
                                                                                                            'uuid' => $uuid,
                                                                                                            'htmlName' => basename($state),
                                                                                                            'userId' => $userId,
                                                                                                            'kelasId' => $kelasId,
                                                                                                        ]) . "' style='width: 100%; height: 600px; border: none; border-radius: 8px;' allow='autoplay; fullscreen'></iframe>"
                                                                                                    );
                                                                                                }
                                                                                            )
                                                                                            ->modalSubmitAction(false)
                                                                                            ->modalCancelAction(false),
                                                                                    ]),
                                                                            ]),

                                                                    ]), // Akhir Detail Elemen

                                                            ]), // Akhir Section Detail Program Pembelajaran

                                                    ]), // Akhir RepeatableEntry Daftar Mapel
                                            ]),

                                        // LOOP LEVEL 3: Kumpulan Mata Pelajaran di dalam Semester

                                    ]), // Akhir RepeatableEntry Materi Ajar (Semester)

                            ]), // Akhir RepeatableEntry Kelas
                    ])->columnSpanFull(),
            ]);
    }
}
