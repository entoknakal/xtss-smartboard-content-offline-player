<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Settings;
use App\Models\User;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = User::query()->where('id', auth()->user()->id)->first();

                switch ($user->role) {
                    case 'guru':
                        return $query->where('id', auth()->user()->id)
                            ->whereNotNull('role');
                        break;
                    case 'admin':
                        return $query->whereIn('role', ['admin', 'guru'])
                            ->whereNot('email', 'superadmin@localhost.com');
                        break;
                    default:
                        return $query;
                }
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('role')
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('role')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // Tambahkan BulkAction untuk sinkronisasi di sini agar bisa mengambil data yang dicentang
                    BulkAction::make('sync_users')
                        ->label('Kirim data pengguna')
                        ->icon('heroicon-o-arrow-trending-up')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Sinkronisasi Pengguna') // Custom judul modal
                        ->modalDescription(
                            function (){
                                $description = 'Apakah Anda yakin ingin mengirim data pengguna yang telah dicentang ke server pusat?<br>Data yang diupdate ke server adalah nama, email dan password.';
                                return new HtmlString($description);
                            }
                        ) // Custom deskripsi
                        ->modalSubmitActionLabel('Ya, Kirim Sekarang') // Custom teks tombol konfirmasi
                        ->action(function (Collection $records) {
                            if ($records->isEmpty()) {
                                Notification::make()
                                    ->title('Peringatan')
                                    ->body('Silakan centang minimal satu user yang ingin dikirim.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            self::syncToOnline($records);
                        }),
                ]),
            ]);
    }

    protected static function syncToOnline(object $users): void
    {
        try {
            $settings = Settings::query()
                ->whereIn('name', ['api_url', 'license_id'])
                ->get(['name', 'uuid']);

            $licenseId = null;
            $apiUrl = null;

            foreach ($settings as $setting) {
                if ($setting->name === 'license_id') {
                    $licenseId = $setting->uuid;
                }
                if ($setting->name === 'api_url') {
                    $apiUrl = $setting->uuid;
                }
            }

            if (empty($apiUrl) || empty($licenseId)) {
                Notification::make()
                    ->title('Gagal')
                    ->body('Konfigurasi API URL atau License ID belum disetel.')
                    ->danger()
                    ->send();
                return;
            }

            $baseUrl = rtrim($apiUrl, '/');

            // Petakan data user yang dicentang
            $payloadUsers = $users->map(function ($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'password' => $user->password, // Kosongkan jika tidak ingin mengubah password di server
                    'role' => $user->role,
                ];
            })->values()->toArray();

            // Kirim data ke API Online
            $response = Http::timeout(15)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'X-License-ID' => $licenseId,
                ])
                ->post("{$baseUrl}/api/sync-user", [
                    'users' => $payloadUsers
                ]);

            if ($response->successful()) {
                Notification::make()
                    ->title('Berhasil')
                    ->body('Data pengguna berhasil disinkronkan ke server pusat.')
                    ->success()
                    ->send();
            } else {
                $errorMsg = $response->json('message') ?? 'Terjadi kesalahan pada server.';
                Notification::make()
                    ->title('Gagal Sinkronisasi')
                    ->body($errorMsg)
                    ->danger()
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->title('Kesalahan Koneksi')
                ->body('Tidak dapat terhubung ke server: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
}
