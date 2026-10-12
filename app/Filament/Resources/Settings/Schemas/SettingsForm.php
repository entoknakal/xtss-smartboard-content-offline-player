<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class SettingsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->readOnly(
                        function () {
                            $role = auth()->user()->role;
                            if (is_null($role)) {
                                return false;
                            }

                            return true;
                        }
                    )
                    ->afterStateUpdated(function (string $state, Set $set) {
                        if ($state === 'api_url') {
                            $set('uuid', config('services.tss_online.url'));
                        }
                    }),
                TextInput::make('uuid')
                    ->url()
                    ->label('Value')
                    ->afterStateHydrated(function (TextInput $component, Get $get, $state) {
                        // dd(config('services.tss_online.url')); // Sembunyikan atau aktifkan untuk test

                        if ($get('name') === 'api_url') {
                            if (is_null($state) || empty($state) || $state == '') {
                                $component->state(config('services.tss_online.url'));
                            }
                        }
                    })
                    ->helperText(
                        function (Get $get) {
                            switch ($get('name')){
                                case 'api_url':
                                    $htmlText = 'Default: ' . config('services.tss_online.url') .
                                        '<br>Klik ikon <x-heroicon-m-arrow-path style="width: 16px; height: 16px; display: inline; vertical-align: middle; color: #d97706;" /> untuk melakukan tes koneksi.' .
                                        '<br>Klik tombol <b>Simpan</b> untuk simpan data.'.
                                        '<br>Bila url api tidak terkoneksi silahkan kontak administrator TSS.';
                                    break;
                                default:
                                    $htmlText = null;
                            }

                            return new HtmlString(Blade::render($htmlText));
                        }
                    )
                    ->suffixAction(
                        Action::make('testConnection')
                            ->label('Tes Koneksi')
                            ->icon('heroicon-m-arrow-path')
                            ->color('primary')
                            ->action(function (Get $get) {
                                $url = $get('uuid');

                                if (blank($url)) {
                                    Notification::make()
                                        ->title('URL Kosong')
                                        ->body('Silakan masukkan URL terlebih dahulu.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                try {
                                    // Kirim HTTP request dengan batas waktu 5 detik
                                    $response = Http::timeout(5)->get($url);

                                    if ($response->successful()) {
                                        Notification::make()
                                            ->title('Koneksi Sukses')
                                            ->body("Berhasil terhubung ke: {$url}")
                                            ->success()
                                            ->send();
                                    } else {
                                        Notification::make()
                                            ->title('Koneksi Gagal')
                                            ->body("Server merespons dengan status: " . $response->status())
                                            ->danger()
                                            ->send();
                                    }
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Koneksi Gagal')
                                        ->body('Tidak dapat menjangkau server. Pastikan URL benar atau server aktif.')
                                        ->danger()
                                        ->send();
                                }
                            })
                    ),
            ]);
    }
}
