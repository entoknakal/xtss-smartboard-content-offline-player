<?php

namespace App\Filament\Pages;

use App\Models\Settings;
use App\Models\User;
use BackedEnum;
use Fahiem\FilamentPinpoint\Pinpoint;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class EnvSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::Cog6Tooth;
    protected string $view = 'filament.pages.env-settings';

    protected static ?string $navigationLabel = 'Pengaturan Sekolah';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return true;
    }

    public function mount(): void
    {
        $settings = Settings::all()->pluck('uuid', 'name')->toArray();

        $this->form->fill([
            'license_id'    => $settings['license_id'] ?? '',
            'nama_sekolah'  => $settings['nama_sekolah'] ?? '',
            'npsn'          => $settings['npsn'] ?? '',
            'status'        => $settings['status'] ?? '',
            'alamat'        => $settings['alamat'] ?? '',
            'lintang'       => $settings['lintang'] ?? '',
            'bujur'         => $settings['bujur'] ?? '',
            'propinsi'      => $settings['propinsi'] ?? '',
            'kota'          => $settings['kota'] ?? '',
            'kecamatan'     => $settings['kecamatan'] ?? '',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextInput::make('license_id')
                                    ->label('ID Lisensi')
                                    ->columnSpanFull()
                                    ->readOnly(function ($state) {
                                        $role = User::query()->where('id', auth()->user()->id)->first()->role;
                                        switch ($role) {
                                            case 'admin':
                                            case 'guru':
                                                return true;
                                                break;
                                            default:
                                                return false;
                                        }
                                    })
                                    // ->live()
                                    ->required(),
                            ])->columnSpanFull(),
                        Grid::make()
                            ->schema([
                                TextInput::make('nama_sekolah')
                                    ->label('Nama Sekolah')
                                    ->readOnly(function ($state) {
                                        $role = User::query()->where('id', auth()->user()->id)->first()->role;
                                        switch ($role) {
                                            case 'guru':
                                                return true;
                                                break;
                                            default:
                                                return false;
                                        }
                                    }),
                                TextInput::make('npsn')
                                    ->label('NPSN')
                                    ->readOnly(function ($state) {
                                        $role = User::query()->where('id', auth()->user()->id)->first()->role;
                                        switch ($role) {
                                            case 'admin':
                                            case 'guru':
                                                return true;
                                                break;
                                            default:
                                                return false;
                                        }
                                    }),

                                TextInput::make('status')
                                    ->label('Status Sekolah (Negeri/Swasta)')
                                    ->readOnly(function ($state) {
                                        $role = User::query()->where('id', auth()->user()->id)->first()->role;
                                        switch ($role) {
                                            case 'admin':
                                            case 'guru':
                                                return true;
                                                break;
                                            default:
                                                return false;
                                        }
                                    }),

                                TextInput::make('alamat')
                                    ->label('Alamat Lengkap')
                                    ->readOnly(function ($state) {
                                        $role = User::query()->where('id', auth()->user()->id)->first()->role;
                                        switch ($role) {
                                            case 'guru':
                                                return true;
                                                break;
                                            default:
                                                return false;
                                        }
                                    }),

                                TextInput::make('propinsi')
                                    ->label('Provinsi')
                                    ->readOnly(function ($state) {
                                        $role = User::query()->where('id', auth()->user()->id)->first()->role;
                                        switch ($role) {
                                            case 'admin':
                                            case 'guru':
                                                return true;
                                                break;
                                            default:
                                                return false;
                                        }
                                    }),

                                TextInput::make('kota')
                                    ->label('Kota/Kabupaten')
                                    ->readOnly(function ($state) {
                                        $role = User::query()->where('id', auth()->user()->id)->first()->role;
                                        switch ($role) {
                                            case 'admin':
                                            case 'guru':
                                                return true;
                                                break;
                                            default:
                                                return false;
                                        }
                                    }),

                                TextInput::make('kecamatan')
                                    ->label('Kecamatan')
                                    ->readOnly(function ($state) {
                                        $role = User::query()->where('id', auth()->user()->id)->first()->role;
                                        switch ($role) {
                                            case 'admin':
                                            case 'guru':
                                                return true;
                                                break;
                                            default:
                                                return false;
                                        }
                                    }),

                                Grid::make(2)
                                    ->schema([
                                        Pinpoint::make('location')
                                            ->belowContent('INFO: Peta akan tampil bila aplikasi terhubung dengan internet.')
                                            ->defaultZoom(16)
                                            ->latField('lintang')
                                            ->lngField('bujur')
                                            ->addressField('alamat')
                                            ->columnSpanFull(),
                                        TextInput::make('lintang')
                                            ->label('Garis Lintang (Latitude)')
                                            ->required(),

                                        TextInput::make('bujur')
                                            ->label('Garis Bujur (Longitude)')
                                            ->required(),
                                    ])->columnSpanFull()
                            ])
                            ->columnSpanFull()
                            ->hidden(fn(Get $get): bool => $get('license_id') === null) // Menjadi hidden jika license_id NULL,
                    ]),
            ])
            ->statePath('data');
    }

    public function save(bool $localOnly = true): bool
    {
        $state = $this->form->getState();

        // ✨ REVISI: Buang payload 'location' dan seluruh field yang berstatus Readonly dari proses update
        unset(
            $state['location'],
            $state['npsn'],
            $state['status'],
            $state['propinsi'],
            $state['kota'],
            $state['kecamatan']
        );

        // Jika user login bukan ID 1 dan license_id sudah terisi di awal, pastikan tidak ikut di-update secara tidak sengaja
        if ((int)auth()->user()->id !== 1 && isset($state['license_id'])) {
            unset($state['license_id']);
        }

        try {
            foreach ($state as $key => $value) {
                $finalValue = ($value === null) ? '' : $value;

                if ($key === 'license_id') {
                    $finalValue = strtoupper($finalValue);
                }

                Settings::query()->where('name', $key)
                    ->update([
                        'uuid' => $finalValue,
                        'updated_at' => now()
                    ]);
            }

            if ($localOnly) {
                Notification::make()
                    ->title('Berhasil!')
                    ->body('Data pengaturan sekolah berhasil diperbarui.')
                    ->success()
                    ->send();
            }

            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal!')
                ->body('Terjadi kesalahan database saat memperbarui data.')
                ->danger()
                ->send();

            return false;
        }
    }

    public function syncToOnline(): void
    {
        // 1. Simpan perubahan ke database lokal terlebih dahulu
        if (! $this->save(localOnly: false)) {
            return; // Batalkan sinkronisasi jika simpan data lokal gagal
        }

        // 2. Ambil data settings terbaru yang baru saja disimpan
        $settings = Settings::all()->pluck('uuid', 'name')->toArray();

        $licenseId   = $settings['license_id'] ?? null;
        $namaSekolah = $settings['nama_sekolah'] ?? null;
        $alamat      = $settings['alamat'] ?? null;
        $lintang     = $settings['lintang'] ?? null;
        $bujur       = $settings['bujur'] ?? null;

        // Validasi minimal license_id harus terisi
        if (empty($licenseId)) {
            Notification::make()
                ->title('Gagal Sinkronisasi')
                ->body('ID Lisensi tidak ditemukan di database lokal.')
                ->warning()
                ->send();
            return;
        }

        try {
            $baseUrl = rtrim(config('services.tss_online.url'), '/');

            // 2. Kirim data ke API Online
            $response = Http::timeout(15)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-License-ID' => $licenseId,
                ])
                ->post("{$baseUrl}/api/sync-sekolah", [
                    'license_id'   => $licenseId,
                    'nama_sekolah' => $namaSekolah,
                    'alamat'       => $alamat,
                    'lintang'      => $lintang,
                    'bujur'        => $bujur,
                ]);

            if ($response->successful()) {
                Notification::make()
                    ->title('Berhasil Sinkronisasi!')
                    ->body($response->json('message') ?? 'Data sekolah berhasil diperbarui ke server online.')
                    ->success()
                    ->send();
            } else {
                $errorMessage = $response->json('message') ?? 'Terjadi kesalahan pada server online.';
                Notification::make()
                    ->title('Gagal Sinkronisasi!')
                    ->body("Server Error ({$response->status()}): {$errorMessage}")
                    ->danger()
                    ->send();
            }
        } catch (ConnectionException $e) {
            $baseUrl = config('services.tss_online.url');

            // 4. Deteksi penyebab kegagalan koneksi
            if ($this->hasInternetConnection()) {
                // Ada internet, tapi server target tidak dapat dijangkau / gagal handshake
                Notification::make()
                    ->title('Gagal Sinkronisasi!')
                    ->body("Data berhasil disimpan di lokal namun Gagal update data ke {$baseUrl}.")
                    ->danger()
                    ->send();
            } else {
                // Tidak ada koneksi internet sama sekali
                Notification::make()
                    ->title('Koneksi Terputus!')
                    ->body('Aplikasi tidak terkoneksi dengan internet.')
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Koneksi Gagal!')
                ->body('Terjadi kesalahan sistem: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Helper untuk memeriksa ketersediaan koneksi internet
     */
    private function hasInternetConnection(): bool
    {
        $connected = @fsockopen("8.8.8.8", 53, $errno, $errstr, 2);
        if ($connected) {
            fclose($connected);
            return true;
        }

        return false;
    }
}
