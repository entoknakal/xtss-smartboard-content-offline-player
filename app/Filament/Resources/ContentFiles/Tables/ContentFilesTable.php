<?php

namespace App\Filament\Resources\ContentFiles\Tables;

use App\Models\MateriAjar;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;
use ZipArchive;

class ContentFilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                function (Builder $query) {
                    $user_id = auth()->user()->id;
                    $user = User::query()->where('id', $user_id)->first();
                    // dd($role->role, $query, $user_id);
                    switch($user->role){
                        case 'guru':
                            return $query->where('user_id', $user_id)->orderBy('updated_at', 'desc');
                            break;
                        default:
                            return $query->orderBy('updated_at', 'desc');
                    }
                }
            )
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Guru')
                    ->sortable(),
                TextColumn::make('file_path')
                    ->label('Nama File'),
                TextColumn::make('file_size_bytes')
                    ->formatStateUsing(fn($state) => Number::fileSize($state, precision: 2))
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('d F Y H:m:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('d F Y H:m:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('restore')
                    ->label('Pulihkan')
                    ->icon(Heroicon::ArrowLeftStartOnRectangle)
                    ->action(
                        function ($record, Page $livewire) {
                            self::extract_file($record);

                            Notification::make()
                                ->title('File berhasil pulihkan')
                                ->success()
                                ->send();

                            // 3. Paksa Livewire untuk merender ulang komponen tabel
                            // $livewire->dispatch('$refresh');
                            $livewire->js('window.location.reload();');
                        }
                    ),
                Action::make('download')
                    ->label('Unduh')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(
                        // fn(array $record) => Storage::disk('public')->download($record['path'])
                        function ($record, Page $livewire) {
                            $file_path = 'ztss_uploads/' . $record->file_path;

                            // 1. Validasi apakah berkas benar-benar ada di storage sebelum diunduh
                            if (!Storage::disk('public')->exists($file_path)) {
                                Notification::make()
                                    ->title('Gagal Mengunduh')
                                    ->body('Berkas fisik tidak ditemukan di server.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            // 2. Tampilkan notifikasi sukses bahwa proses unduhan berhasil dipicu
                            Notification::make()
                                ->title('Unduhan Berhasil Dimulai')
                                ->body("Berkas {$record->file_path} sedang dikirim ke perangkat Anda.")
                                ->success()
                                ->send();

                            // 3. Ambil URL file publik dan perintahkan browser melakukan download lewat JavaScript link sandbox
                            $downloadUrl = Storage::disk('public')->url($file_path);

                            $livewire->js("
                                let link = document.createElement('a');
                                link.href = '{$downloadUrl}';
                                link.download = '" . basename($record->file_path) . "';
                                document.body.appendChild(link);
                                link.click();
                                document.body.removeChild(link);
                            ");
                        }
                    ),
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function extract_file($record)
    {
        $file_path = 'ztss_uploads/' . $record->file_path;
        $full_path = Storage::disk('public')->path($file_path);

        $zip = new ZipArchive();
        if ($zip->open($full_path) === true) {
            $jsonData = $zip->getFromName('data.json');
            $zip->close();
        } else {
            session()->flash('error', 'Gagal membuka berkas ZTSS.');
            return;
        }

        $data = json_decode($jsonData, true);

        $extractFolderName = array_key_first($data);

        $relativeExtractPath = 'ztss_extracted/' . $extractFolderName;
        $absoluteExtractPath = Storage::disk('public')->path($relativeExtractPath);

        $zipExtract = new ZipArchive();
        if ($zipExtract->open($full_path) === true) {
            $zipExtract->extractTo($absoluteExtractPath);
            $zipExtract->close();
        } else {
            session()->flash('error', 'Gagal mengekstrak berkas ZTSS.');
            return;
        }

        $arr_mapel = [];
        $arr_materi = [];
        $user_id = null;
        $sekolah_status = null;

        foreach ($data as $uuid => $arr_contents) {
            $user_name = $arr_contents['user_name'];
            $user_email = $arr_contents['user_email'];
            $user_password = $arr_contents['user_password'];

            $find_user = User::query()->where('email', $user_email);

            if ($find_user->count() === 0) {
                $user = new User();
                $user->name = $user_name;
                $user->email = $user_email;
                $user->password = $user_password;
                $user->role = 'guru';

                $user->save();

                $user_id = $user->id;
            } else {
                $user_id = $find_user->first()->id;
            }

            // $sekolah_id = $arr_contents['sekolah_id'];
            $sekolah_id = $extractFolderName;
            $sekolah_name = $arr_contents['nama_sekolah'];
            $sekolah_npsn = $arr_contents['sekolah_npsn'];
            $sekolah_status = match ($arr_contents['sekolah_status']) {
                'S' => 'Swasta',
                'N' => 'Negeri',
                default => null,
            };
            $sekolah_alamat = $arr_contents['sekolah_alamat'];
            $sekolah_lintang = $arr_contents['sekolah_lintang'];
            $sekolah_bujur = $arr_contents['sekolah_bujur'];
            $sekolah_propinsi = $arr_contents['sekolah_propinsi'];
            $sekolah_kota = $arr_contents['sekolah_kota'];
            $sekolah_kecamatan = $arr_contents['sekolah_kecamatan'];




            $useridFolderPath = $relativeExtractPath . '/' . $arr_contents['user_id'];
            if (!Storage::disk('public')->exists($useridFolderPath)) {
                Storage::disk('public')->makeDirectory($useridFolderPath);
            }

            // copy file data.json ke folder user_id
            Storage::disk('public')->copy($relativeExtractPath . '/data.json', $useridFolderPath . '/data.json');

            if (is_array($arr_contents['kelas'])) {
                foreach ($arr_contents['kelas'] as $arr_kelas) {
                    $kelas_id = $arr_kelas['kelas_id'];
                    $nama_kelas = $arr_kelas['nama_kelas'];

                    $kelasIdFolderPath = $useridFolderPath . '/' . $arr_kelas['kelas_id'];
                    if (!Storage::disk('public')->exists($kelasIdFolderPath)) {
                        Storage::disk('public')->makeDirectory($kelasIdFolderPath);
                    }
                    // dd($arr_kelas);

                    if (is_array($arr_kelas['materi_ajar'])) {
                        foreach ($arr_kelas['materi_ajar'] as $key => $arr_materi_ajar) {

                            $materi_ajar_id = $arr_materi_ajar['materi_ajar_id'];
                            $mata_pelajaran_id = $arr_materi_ajar['mata_pelajaran_id'];
                            $mata_pelajaran = $arr_materi_ajar['mata_pelajaran'];
                            $kategori_mata_pelajaran = $arr_materi_ajar['kategori_mata_pelajaran'];
                            $semester_id = $arr_materi_ajar['semester_id'];
                            $semester = $arr_materi_ajar['semester'];
                            $elemen_pembelajaran = $arr_materi_ajar['elemen_pembelajaran'];
                            $deskripsi_elemen_pembelajaran = $arr_materi_ajar['deskripsi_elemen_pembelajaran'];
                            $materi_ajar = $arr_materi_ajar['materi'];

                            $author = $arr_materi_ajar['author'];


                            $konten_file_path = [];
                            foreach ($arr_materi_ajar['konten_file_path'] as $values) {
                                $file_konten = basename($values);
                                $from = $relativeExtractPath . '/' . $file_konten;
                                $to = $kelasIdFolderPath . '/' . $file_konten;

                                $arr_materi[$arr_contents['user_id']][$arr_kelas['kelas_id']][] = $file_konten;

                                // copy konten dari folder uuid sekolah ke folder kelas_id
                                Storage::disk('public')->copy($from, $to);

                                $konten_file_path[] = $to;
                            }
                            $arr_mapel[$key] = [
                                'uuid' => $sekolah_id,
                                'user_id' => $user_id,
                                'kelas_id' => $kelas_id,
                                'nama_kelas' => $nama_kelas,
                                'mata_pelajaran_id' => $mata_pelajaran_id,
                                'mata_pelajaran' => $mata_pelajaran,
                                'semester_id' => $semester_id,
                                'semester' => $semester,
                                'materi_ajar_id' => $materi_ajar_id,
                                'materi_ajar' => $materi_ajar,
                                'elemen_pembelajaran' => $elemen_pembelajaran,
                                'deskripsi_elemen_pembelajaran' => $deskripsi_elemen_pembelajaran,
                                'kategori_mata_pelajaran' => $kategori_mata_pelajaran,
                                'konten_file_path' => json_encode($konten_file_path),
                                'author' => $author,
                            ];
                        }
                    }
                }
            }
        }

        foreach ($arr_mapel as $mapel) {
            MateriAjar::updateOrCreate(
                // Argumen 1: Kunci unik untuk mendeteksi data yang sama
                [
                    'uuid'              => $mapel['uuid'],
                    'user_id'           => $mapel['user_id'],
                    'kelas_id'          => $mapel['kelas_id'],
                    'mata_pelajaran_id' => $mapel['mata_pelajaran_id'],
                    'semester_id'       => $mapel['semester_id'],
                    'materi_ajar_id'    => $mapel['materi_ajar_id'],
                ],
                // Argumen 2: Data pelengkap yang akan di-insert atau di-update jika bentrok
                [
                    'nama_kelas'                    => $mapel['nama_kelas'],
                    'mata_pelajaran'                => $mapel['mata_pelajaran'],
                    'semester'                      => $mapel['semester'],
                    'materi_ajar'                   => $mapel['materi_ajar'],
                    'elemen_pembelajaran'           => $mapel['elemen_pembelajaran'],
                    'deskripsi_elemen_pembelajaran' => $mapel['deskripsi_elemen_pembelajaran'],
                    'kategori_mata_pelajaran'       => $mapel['kategori_mata_pelajaran'],
                    'konten_file_path'              => json_decode($mapel['konten_file_path'], true), // Dikembalikan ke array bersih agar dicasting otomatis oleh Model
                    'author'                        => $mapel['author'],
                ]
            );
        }

        // hapus konten yang berada di uuid sekolah
        foreach ($arr_materi as $userid => $arr_kelas) {
            foreach ($arr_kelas as $kelasid => $arr_materi) {
                foreach ($arr_materi as $materi) {
                    Storage::disk('public')->delete($relativeExtractPath . '/' . $materi);
                }
            }
        }

        // hapus file data.json yang ada di folder uuid sekolah
        Storage::disk('public')->delete($relativeExtractPath . '/data.json');

        /* hapus temporary file upload yang ada di folder storage\app\private\livewire-tmp */
        $livewireTmpPath = storage_path('app/private/livewire-tmp');
        // Memastikan folder tersebut ada sebelum dibersihkan agar tidak memicu error
        if (File::exists($livewireTmpPath)) {
            File::cleanDirectory($livewireTmpPath);
        }

        // dd(
        //     // $record,
        //     // $data,
        //     $extractFolderName,
        // );
    }
}
