<?php

namespace App\Livewire;

use App\Models\ContentFiles;
use App\Models\MateriAjar;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use ZipArchive;

class FileUploadZtss extends Component
{
    use WithFileUploads;

    public $ztssFile;
    public $licenseError = null;

    protected function rules()
    {
        $maxSize = ini_get('upload_max_filesize');
        $suffix = strtoupper(substr($maxSize, -1));
        $value = (int) $maxSize;

        $maxKb = match ($suffix) {
            'G' => $value * 1024 * 1024,
            'M' => $value * 1024,
            'K' => $value,
            default => $value * 1024,
        };

        return [
            'ztssFile' => [
                'required',
                'file',
                'max:' . $maxKb,
                Rule::file()->extensions(['ztss']), // Hanya menerima ekstensi .ztss
            ],
        ];
    }

    public function messages()
    {
        return [
            'ztssFile.required' => 'Silakan pilih file terlebih dahulu.',
            'ztssFile.file' => 'Input harus berupa file.',
            'ztssFile.max' => 'Ukuran file tidak boleh lebih dari ' . ini_get('upload_max_filesize') . '.',
        ];
    }

    public function updatedZtssFile()
    {
        $this->licenseError = null;

        try {
            // Jalankan validasi dasar (ukuran & ekstensi)
            $this->validateOnly('ztssFile');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Jika gagal validasi dasar (misal file kebesaran), biarkan sistem validasi bawaan yang bekerja
            throw $e;
        }

        try {
            $temporaryFullPath = $this->ztssFile->getRealPath();

            $zip = new ZipArchive();
            if ($zip->open($temporaryFullPath) === true) {
                $jsonData = $zip->getFromName('data.json');
                $zip->close();
            } else {
                $this->setLicenseError('Gagal membuka berkas ZTSS.');
                return;
            }

            if ($jsonData === false) {
                $this->setLicenseError('File data.json tidak ditemukan di dalam file .ztss');
                return;
            }

            $data = json_decode($jsonData, true);
            if (empty($data)) {
                $this->setLicenseError('File data.json kosong atau format tidak valid.');
                return;
            }

            $extractFolderName = array_key_first($data);

            $check_data = Settings::query()->where('name', '=', 'license_id')
                ->where('uuid', '=', $extractFolderName);

            if ($check_data->count() === 0) {
                // Gunakan pesan error HTML tebal yang Anda inginkan
                $this->setLicenseError('Lisensi konten ztss <b>tidak sesuai</b> dengan lisensi aplikasi.<br>Silahkan unggah konten ztss lainnya.');
                return;
            }
        } catch (\Exception $e) {
            $this->setLicenseError('Gagal membaca lisensi file: ' . $e->getMessage());
        }
    }

    // Fungsi helper baru untuk mencatat error, menghapus file fisik, dan mereset progress
    private function setLicenseError($message)
    {
        $this->licenseError = $message; // Simpan pesan error agar menetap di server

        if ($this->ztssFile) {
            $this->ztssFile->delete();  // Hapus file temporary di disk
            $this->ztssFile = null;     // Kosongkan file agar UI kembali ke tulisan "Klik atau seret file"
        }

        // Kirim sinyal ke Alpine untuk memaksa progress bar kembali ke 0
        $this->dispatch('reset-upload-progress');
    }

    public function save()
    {
        $this->validate();

        try {
            $filename = $this->ztssFile->getClientOriginalName();

            // Pastikan direktori ada sebelum menyimpan
            if (!Storage::disk('public')->exists('ztss_uploads')) {
                Storage::disk('public')->makeDirectory('ztss_uploads');
            }
            $path = $this->ztssFile->storeAs('ztss_uploads', $filename, 'public');

            // Cek file ztss apakah sudah ada dalam database?
            $basename_path = basename($path);
            $count_content_exists = ContentFiles::where('file_path', $basename_path)->count();
            if($count_content_exists > 0) {
                session()->flash('error', "File {$basename_path} sudah pernah di unggah.<br>Silahkan coba file lainnya.");
                return;
            }

            // Mengambil absolute path file yang baru diunggah
            $fullPath = Storage::disk('public')->path($path);

            // 1. Membaca data.json langsung dari dalam arsip ZIP
            $zip = new ZipArchive();
            if ($zip->open($fullPath) === true) {
                $jsonData = $zip->getFromName('data.json');
                $zip->close();
            } else {
                session()->flash('error', 'Gagal membuka berkas ZTSS.');
                return;
            }

            // Validasi keberadaan data.json
            if ($jsonData === false) {
                session()->flash('error', 'File data.json tidak ditemukan di dalam file .ztss');
                return;
            }

            $data = json_decode($jsonData, true);
            if (empty($data)) {
                session()->flash('error', 'File data.json kosong atau format tidak valid.');
                return;
            }

            // Ambil key pertama dari data JSON sebagai nama folder
            $extractFolderName = array_key_first($data);

            $relativeExtractPath = 'ztss_extracted/' . $extractFolderName;
            $absoluteExtractPath = Storage::disk('public')->path($relativeExtractPath);

            if (!Storage::disk('public')->exists($relativeExtractPath)) {
                Storage::disk('public')->makeDirectory($relativeExtractPath);
            }

            // 2. Ekstrak seluruh isi file menggunakan ZipArchive ke folder tujuan
            $zipExtract = new ZipArchive();
            if ($zipExtract->open($fullPath) === true) {
                $zipExtract->extractTo($absoluteExtractPath);
                $zipExtract->close();
            } else {
                session()->flash('error', 'Gagal mengekstrak berkas ZTSS.');
                return;
            }

            $arr_materi = [];
            $arr_mapel = [];
            $user_id = null;
            $sekolah_status = null;

            // dd($data);
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
                                $arr_mapel[] = [
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

            // 2. PERBAIKAN UTAMA: Ganti MateriAjar::upsert dengan perulangan updateOrCreate
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

            //masukkan dalam table content_files
            $insert_file = new ContentFiles();
            $insert_file->user_id = $user_id;
            $insert_file->file_path = $basename_path;
            $insert_file->data_json = json_decode($jsonData);
            $insert_file->file_size_bytes = Storage::disk('public')->size($path);
            $insert_file->save();
            // Tidak diperlukan lagi karena sudah di handle LicenseForm

            // $data_sekolah = [
            //     ['name' => 'nama_sekolah', 'uuid' => $sekolah_name],
            //     ['name' => 'npsn', 'uuid' => $sekolah_npsn],
            //     ['name' => 'status', 'uuid' => $sekolah_status],
            //     ['name' => 'alamat', 'uuid' => $sekolah_alamat],
            //     ['name' => 'lintang', 'uuid' => $sekolah_lintang],
            //     ['name' => 'bujur', 'uuid' => $sekolah_bujur],
            //     ['name' => 'propinsi', 'uuid' => $sekolah_propinsi],
            //     ['name' => 'kota', 'uuid' => $sekolah_kota],
            //     ['name' => 'kecamatan', 'uuid' => $sekolah_kecamatan],
            // ];


            // foreach ($data_sekolah as $data) {
            //     Settings::updateOrCreate(
            //         [
            //             'name' => $data['name'],
            //         ],
            //         [
            //             'uuid' => $data['uuid'],
            //         ]
            //     );
            // }

            // hapus konten yang berada di uuid sekolah
            foreach ($arr_materi as $userid => $arr_kelas) {
                foreach ($arr_kelas as $kelasid => $files) {
                    foreach ($files as $materi) {
                        Storage::disk('public')->delete($relativeExtractPath . '/' . $materi);
                    }
                }
            }

            // hapus file data.json yang ada di folder uuid sekolah
            Storage::disk('public')->delete($relativeExtractPath . '/data.json');

            session()->flash('message', 'File ' . $filename . ' berhasil di unggah. Silahkan klik tombol <b>Mulai Belajar</b> untuk akses konten');

            /* hapus temporary file upload yang ada di folder storage\app\private\livewire-tmp */
            $this->ztssFile->delete();

            $this->reset('ztssFile'); // Bersihkan input file setelah berhasil diunggah
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengunggah file: ' . $e->getMessage());
        }
    }

    private function cleanupFailedUpload()
    {
        if ($this->ztssFile) {
            $this->ztssFile->delete(); // Hapus file fisik di storage temporary
            $this->ztssFile = null;    // Kosongkan properti agar UI kembali ke keadaan awal (Klik atau seret file)
        }

        // Kirim sinyal ke Alpine untuk memaksa progress menjadi 0
        $this->dispatch('reset-upload-progress');
    }

    public function render()
    {
        return view('livewire.file-upload-ztss', [
            'maxSize' => ini_get('upload_max_filesize')
        ]);
    }
}
