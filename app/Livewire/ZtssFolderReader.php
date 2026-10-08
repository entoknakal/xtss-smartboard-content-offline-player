<?php

namespace App\Livewire;

use App\Models\Settings;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ZtssFolderReader extends Component
{
    public $files = [];
    public string $uuid;
    public string $nama_sekolah;
    public $teachers = [];
    public $classes = [];
    public $subjects = [];
    public $elemenList = [];
    public $selectedTeacher = '';
    public $selectedClass = '';
    public $selectedMapel = '';
    public $selectedElemenPembelajaran = '';
    public $elemenDetails = []; // Stores the full materi_ajar item for each elemen
    public $selectedElemenMateri = null; // Stores structured data for the currently selected elemen
    public $selectedContent = null;
    public $mapelFileMapping = [];
    public $elemenFileMapping = [];
    public $showFullscreenModal = false;
    public $modalContentUrl = '';
    public $modalFileName = '';

    public $statusMessage = '';
    public $statusType = '';

    public function mount()
    {
        // $this->uuid = config('services.uuid_sekolah') ?? basename(Storage::disk('public')->directories('ztss_extracted')[0] ?? null);
        $license_id = Settings::where('name', 'license_id')->first()->uuid;

        $this->uuid = $license_id;
        // $this->uuid = env('UUID_SCHOOL_DEFAULT');

        if (is_null($this->uuid)) {
            $this->statusMessage = 'Kontak Administrator untuk mengaktifkan lisensi aplikasi.';
            $this->statusType = 'error';
            return;
        }

        $newValue = basename(Storage::disk('public')->directories('ztss_extracted')[0] ?? null);

        if ($this->uuid !== $newValue) {
            $this->statusMessage = 'Belum ada data yang di upload. Silahkan upload file *ztss di halaman Beranda.';
            $this->statusType = 'error';
            return;
        }

        // UUID_SCHOOL_DEFAULT="3AC663C1-C1B4-4F6E-ACC0-A2CD3F3F8B03" // contoh
        // $newValue = basename(Storage::disk('public')->directories('ztss_extracted')[0] ?? null);
        // $key = 'UUID_SCHOOL_DEFAULT';
        // $envPath = base_path('.env');

        // if (!empty($newValue)) {
        //     // 1. Validasi apakah file .env ada
        //     if (!File::exists($envPath)) {
        //         $this->statusMessage = '.env file tidak ditemukan.';
        //         $this->statusType = 'error';
        //         return;
        //     }

        //     $envContent = File::get($envPath);
        //     // 2. Periksa apakah KEY sudah ada di dalam file .env
        //     if (str_contains($envContent, "{$key}=")) {
        //         // Berhenti di sini jika sudah ada (Tidak di-replace)
        //         $this->statusMessage = "Variabel {$key} sudah terdaftar dan tidak dapat diganti.";
        //         $this->statusType = 'warning';

        //         $this->uuid = config('services.uuid_sekolah') ?: env('UUID_SCHOOL_DEFAULT');
        //         // return;

        //     } else {
        //         // 3. Jika belum ada, tambahkan baris baru di paling bawah file
        //         $updatedContent = rtrim($envContent) . "\n{$key}=\"{$newValue}\"\n";

        //         // Tulis data baru ke file .env
        //         File::put($envPath, $updatedContent);

        //         // 4. Bersihkan config cache agar nilai baru langsung terbaca
        //         Artisan::call('config:clear');

        //         // Set pesan sukses
        //         $this->statusMessage = "Variabel {$key} berhasil ditambahkan!";
        //         $this->statusType = 'success';
        //         // $this->uuid = config('services.uuid_sekolah');

        //         $this->uuid = $newValue;
        //     }
        // }


        $this->loadData();
        $this->loadFiles();
    }

    public function loadData()
    {
        $directory = 'ztss_extracted/' . $this->uuid;
        if (!Storage::disk('public')->exists($directory)) return;

        // Cari semua file data.json di dalam folder UUID
        $jsonFiles = Storage::disk('public')->allFiles($directory);
        $jsonFiles = array_filter($jsonFiles, fn($f) => basename($f) === 'data.json');

        $teachers = [];
        $classes = [];
        $subjects = [];
        $mapping = [];
        $elemenList = [];
        $elemenMapping = [];
        $elemenDetails = []; // Temporary array to build the structure

        foreach ($jsonFiles as $file) {
            $jsonData = Storage::disk('public')->get($file);
            $content = json_decode($jsonData, true);
            if (!$content || !is_array($content)) continue;

            foreach ($content as $item) {
                // Ambil data Guru
                $this->nama_sekolah = $item['nama_sekolah'] ?? $this->nama_sekolah;
                $uid = $item['user_id'] ?? null;
                if ($uid) {
                    $teachers[$uid] = $item['user_name'] ?? $uid;

                    // Ambil data Kelas
                    if (isset($item['kelas']) && is_array($item['kelas'])) {
                        foreach ($item['kelas'] as $k) {
                            $kid = $k['kelas_id'] ?? null;
                            if (!$kid) continue;

                            $classes[$kid] = [
                                'name' => $k['nama_kelas'] ?? $kid,
                                'user_id' => $uid
                            ];

                            // Ambil data Mata Pelajaran dari materi_ajar
                            if (isset($k['materi_ajar']) && is_array($k['materi_ajar'])) {
                                foreach ($k['materi_ajar'] as $ma) {
                                    $mapel = $ma['mata_pelajaran'] ?? null;
                                    $elemen = $ma['elemen_pembelajaran'] ?? null;
                                    if ($mapel) {
                                        // Simpan list mapel unik per guru & kelas
                                        if (!isset($subjects[$uid][$kid]) || !in_array($mapel, $subjects[$uid][$kid])) {
                                            $subjects[$uid][$kid][] = $mapel;
                                        }

                                        // Petakan file mana saja yang masuk ke mapel ini
                                        if (isset($ma['konten_file_path']) && is_array($ma['konten_file_path'])) {
                                            foreach ($ma['konten_file_path'] as $path) {
                                                $filename = basename($path);
                                                $mapping[$uid][$kid][$mapel][] = $filename;

                                                // Petakan file ke elemen pembelajaran jika ada
                                                if ($elemen) {
                                                    $elemenMapping[$uid][$kid][$mapel][$elemen][] = $filename;
                                                }
                                            }
                                        }

                                        // Simpan list elemen unik per mapel
                                        if ($elemen) {
                                            if (!isset($elemenList[$uid][$kid][$mapel]) || !in_array($elemen, $elemenList[$uid][$kid][$mapel])) {
                                                $elemenList[$uid][$kid][$mapel][] = $elemen;
                                            }
                                            // Store the full materi_ajar item for later retrieval
                                            $elemenDetails[$uid][$kid][$mapel][$elemen] = $ma;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->teachers = $teachers;
        $this->classes = $classes;
        $this->subjects = $subjects;
        $this->mapelFileMapping = $mapping;
        $this->elemenList = $elemenList;
        $this->elemenFileMapping = $elemenMapping;
        $this->elemenDetails = $elemenDetails; // Assign to public property
    }

    public function updatedSelectedTeacher()
    {
        $this->selectedClass = '';
        $this->selectedMapel = '';
        $this->selectedElemenPembelajaran = '';
        $this->loadFiles();
    }

    public function updatedSelectedClass()
    {
        $this->selectedMapel = '';
        $this->selectedElemenPembelajaran = '';
        $this->loadFiles();
    }

    public function updatedSelectedMapel()
    {
        $this->selectedElemenPembelajaran = '';
        $this->loadFiles();
    }

    public function updatedSelectedElemenPembelajaran()
    {
        $this->loadFiles();
    }

    public function loadFiles()
    {
        $directory = 'ztss_extracted/' . $this->uuid;
        $this->files = [];
        $this->selectedElemenMateri = null; // Reset detailed content
        $this->selectedContent = null; // Reset content

        if ($this->selectedTeacher && $this->selectedClass && $this->selectedMapel && $this->selectedElemenPembelajaran) {
            // Scenario 1: Detailed Elemen View
            $details = $this->elemenDetails[$this->selectedTeacher][$this->selectedClass][$this->selectedMapel][$this->selectedElemenPembelajaran] ?? null;

            if ($details) {
                $this->selectedElemenMateri = [
                    'elemen_pembelajaran' => $details['elemen_pembelajaran'] ?? 'N/A',
                    'kategori_mata_pelajaran' => $details['kategori_mata_pelajaran'] ?? 'N/A',
                    'semester' => $details['semester'] ?? 'N/A',
                    'deskripsi_elemen_pembelajaran' => $details['deskripsi_elemen_pembelajaran'] ?? 'N/A',
                    'materi' => [],
                    'konten' => [],
                ];

                if (isset($details['materi']) && is_array($details['materi'])) {
                    foreach ($details['materi'] as $materiItem) {
                        $this->selectedElemenMateri['materi'][] = [
                            'nama_bab' => $materiItem['nama_bab'] ?? 'N/A',
                            'tujuan_pembelajaran' => strip_tags($materiItem['tujuan_pembelajaran'] ?? 'N/A'),
                            'alokasi_waktu' => isset($materiItem['alokasi_waktu']) ? $materiItem['alokasi_waktu'] . ' JP' : null,
                        ];
                    }
                }

                if (!empty($details['konten_file_path']) && is_array($details['konten_file_path'])) {
                    $this->selectedContent = ['konten' => []];
                    foreach ($details['konten_file_path'] as $path) {
                        $this->selectedContent['konten'][] = [
                            'file_name' => basename($path),
                            // 'fullFilePath' => asset('storage/ztss_extracted/' . $this->uuid . '/' . $this->selectedTeacher . '/' . $this->selectedClass . '/' . basename($path)),
                            'fullFilePath' => Storage::url('ztss_extracted/' . $this->uuid . '/' . $this->selectedTeacher . '/' . $this->selectedClass . '/' . basename($path)),
                        ];
                    }
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.ztss-folder-reader');
    }

    /**
     * Opens the fullscreen modal with the given file URL.
     *
     * @param string $url The full URL to the file.
     * @param string $fileName The name of the file.
     * @return void
     */
    public function openFileInModal($url, $fileName = '')
    {
        $this->modalContentUrl = $url;
        $this->modalFileName = $fileName;
        $this->showFullscreenModal = true;
    }

    /**
     * Closes the fullscreen modal and resets the content URL.
     *
     * @return void
     */
    public function closeFullscreenModal()
    {
        $this->showFullscreenModal = false;
        $this->modalContentUrl = '';
        $this->modalFileName = '';
    }
}
