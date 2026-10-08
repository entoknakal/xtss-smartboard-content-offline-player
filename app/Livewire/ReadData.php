<?php

namespace App\Livewire;

use App\Models\MateriAjar;
use App\Models\Settings;
use Livewire\Component;

class ReadData extends Component
{
    public ?string $uuid = null;
    public ?string $nama_sekolah = null;
    
    // Properties untuk menyimpan pilihan dropdown (v-model)
    public ?string $selectedGuru = '';
    public ?string $selectedKelas = '';
    public ?string $selectedMapel = '';
    public ?string $selectedSemester = '';
    public ?string $selectedElemen = '';

    // Properties untuk menampung list array data dropdown
    public array $list_guru = [];
    public array $list_kelas = [];
    public array $list_mapel = [];
    public array $list_semester = [];
    public array $list_elemen = [];

    public ?array $selectedElemenMateri = null;
    public bool $showFullscreenModal = false;
    public string $modalFileName = '';
    public string $modalContentUrl = '';

    public function mount()
    {
        $license_id = Settings::query()->where('name', 'license_id')->first()->uuid;
        $this->uuid = $license_id;

        $this->loadSekolah();
        $this->loadGuru();
    }

    public function loadSekolah()
    {
        $this->nama_sekolah = Settings::query()->where('name', 'nama_sekolah')->first()->uuid;
    }

    // 1. Load data Guru di awal
    public function loadGuru()
    {
        $this->list_guru = MateriAjar::where('uuid', $this->uuid)
            ->whereHas('user', function ($query) {
                $query->where('role', 'guru');
            })
            ->with(['user:id,name'])
            ->get()
            ->pluck('user')
            ->unique('id')
            ->pluck('name', 'id')
            ->toArray();
    }

    // Hook: Otomatis berjalan saat selectedGuru berubah
    // public function updatedSelectedGuru($value)
    // {
    //     // Reset semua pilihan dan list di bawahnya
    //     $this->reset(['selectedKelas', 'selectedMapel', 'selectedSemester', 'selectedElemen', 'list_kelas', 'list_mapel', 'list_semester', 'list_elemen']);

    //     if (!empty($value)) {
    //         $this->list_kelas = MateriAjar::where('uuid', $this->uuid)
    //             ->where('user_id', $value)
    //             ->get()
    //             ->unique('kelas_id')
    //             ->pluck('nama_kelas', 'kelas_id')
    //             ->toArray();
    //     }
    // }
    public function updatedSelectedGuru($value)
    {
        $this->reset(['selectedKelas', 'selectedMapel', 'selectedSemester', 'selectedElemen', 'selectedElemenMateri', 'list_kelas', 'list_mapel', 'list_semester', 'list_elemen']);

        if (!empty($value)) {
            $this->list_kelas = MateriAjar::where('uuid', $this->uuid)
                ->where('user_id', (int)$value)
                ->get()
                ->unique('kelas_id')
                ->pluck('nama_kelas', 'kelas_id')
                ->toArray();
        }
    }

    // Hook: Otomatis berjalan saat selectedKelas berubah
    // public function updatedSelectedKelas($value)
    // {
    //     $this->reset(['selectedMapel', 'selectedSemester', 'selectedElemen', 'list_mapel', 'list_semester', 'list_elemen']);

    //     if (!empty($value)) {
    //         $this->list_mapel = MateriAjar::where('uuid', $this->uuid)
    //             ->where('user_id', $this->selectedGuru)
    //             ->where('kelas_id', $value)
    //             ->get()
    //             ->unique('mata_pelajaran_id')
    //             ->pluck('mata_pelajaran', 'mata_pelajaran_id')
    //             ->toArray();
    //     }
    // }
    public function updatedSelectedKelas($value)
    {
        $this->reset(['selectedMapel', 'selectedSemester', 'selectedElemen', 'selectedElemenMateri', 'list_mapel', 'list_semester', 'list_elemen']);

        if (!empty($value)) {
            $this->list_mapel = MateriAjar::where('uuid', $this->uuid)
                ->where('user_id', (int)$this->selectedGuru)
                ->where('kelas_id', (int)$value)
                ->get()
                ->unique('mata_pelajaran_id')
                ->pluck('mata_pelajaran', 'mata_pelajaran_id')
                ->toArray();
        }
    }

    // Hook: Otomatis berjalan saat selectedMapel berubah
    // public function updatedSelectedMapel($value)
    // {
    //     $this->reset(['selectedSemester', 'selectedElemen', 'list_semester', 'list_elemen']);

    //     if (!empty($value)) {
    //         $this->list_semester = MateriAjar::where('uuid', $this->uuid)
    //             ->where('user_id', $this->selectedGuru)
    //             ->where('kelas_id', $this->selectedKelas)
    //             ->where('mata_pelajaran_id', $value)
    //             ->get()
    //             ->unique('semester_id')
    //             ->pluck('semester', 'semester_id')
    //             ->toArray();
    //     }
    // }
    public function updatedSelectedMapel($value)
    {
        $this->reset(['selectedSemester', 'selectedElemen', 'selectedElemenMateri', 'list_semester', 'list_elemen']);

        if (!empty($value)) {
            $this->list_semester = MateriAjar::where('uuid', $this->uuid)
                ->where('user_id', (int)$this->selectedGuru)
                ->where('kelas_id', (int)$this->selectedKelas)
                ->where('mata_pelajaran_id', (int)$value)
                ->get()
                ->unique('semester_id')
                ->pluck('semester', 'semester_id')
                ->toArray();
        }
    }

    // Hook: Otomatis berjalan saat selectedSemester berubah
    // public function updatedSelectedSemester($value)
    // {
    //     $this->reset(['selectedElemen', 'list_elemen']);

    //     if (!empty($value)) {
    //         // Mengambil elemen_pembelajaran yang unik berdasarkan filter di atas
    //         $this->list_elemen = MateriAjar::where('uuid', $this->uuid)
    //             ->where('user_id', $this->selectedGuru)
    //             ->where('kelas_id', $this->selectedKelas)
    //             ->where('mata_pelajaran_id', $this->selectedMapel)
    //             ->where('semester_id', $value)
    //             ->get()
    //             ->unique('elemen_pembelajaran')
    //             ->pluck('elemen_pembelajaran', 'elemen_pembelajaran') // Key & value disamakan karena berupa string text teks elemen
    //             ->toArray();
    //     }
    // }
    public function updatedSelectedSemester($value)
    {
        $this->reset(['selectedElemen', 'selectedElemenMateri', 'list_elemen']);

        if (!empty($value)) {
            $this->list_elemen = MateriAjar::where('uuid', $this->uuid)
                ->where('user_id', (int)$this->selectedGuru)
                ->where('kelas_id', (int)$this->selectedKelas)
                ->where('mata_pelajaran_id', (int)$this->selectedMapel)
                ->where('semester_id', (int)$value)
                ->get()
                ->unique('elemen_pembelajaran')
                ->pluck('elemen_pembelajaran', 'elemen_pembelajaran')
                ->toArray();
        }
    }

    // ✨ TAMBAHAN: Hook ketika elemen pembelajaran dipilih untuk memuat Content Area
    // public function updatedSelectedElemen($value)
    // {
    //     $this->reset(['selectedElemenMateri']);

    //     if (!empty($value)) {
    //         $materiRow = MateriAjar::where('uuid', $this->uuid)
    //             ->where('user_id', $this->selectedGuru)
    //             ->where('kelas_id', $value)
    //             ->where('mata_pelajaran_id', $this->selectedMapel)
    //             ->where('semester_id', $this->selectedSemester)
    //             ->where('elemen_pembelajaran', $value)
    //             ->first();

    //         if ($materiRow) {
    //             // ✨ 1. Ambil langsung data materi_ajar karena otomatis berupa Array PHP berkat $casts model
    //             $materiArray = $materiRow->materi_ajar;
    //             if (is_string($materiArray)) {
    //                 $materiArray = json_decode($materiArray, true);
    //             }
                
    //             // ✨ 2. Ambil langsung data konten_file_path karena otomatis berupa Array PHP berkat $casts model
    //             $filesArray = $materiRow->konten_file_path;
    //             if (is_string($filesArray)) {
    //                 $filesArray = json_decode($filesArray, true);
    //             }

    //             $formattedKonten = [];
    //             if (is_array($filesArray)) {
    //                 foreach ($filesArray as $filePath) {
    //                     $formattedKonten[] = [
    //                         'file_name' => basename($filePath),
    //                         'fullFilePath' => asset('storage/' . $filePath)
    //                     ];
    //                 }
    //             }

    //             $this->selectedElemenMateri = [
    //                 'semester' => $materiRow->semester,
    //                 'kategori_mata_pelajaran' => $materiRow->kategori_mata_pelajaran,
    //                 'elemen_pembelajaran' => $materiRow->elemen_pembelajaran,
    //                 'deskripsi_elemen_pembelajaran' => $materiRow->deskripsi_elemen_pembelajaran,
    //                 'materi' => is_array($materiArray) ? $materiArray : [],
    //                 'konten' => $formattedKonten
    //             ];
    //         }
    //     }
    // }
    public function updatedSelectedElemen($value)
    {
        $this->reset(['selectedElemenMateri']);

        if (!empty($value)) {
            // ✨ PERBAIKAN UTAMA: pastikan menggunakan $this->selectedKelas (berisi angka id kelas), bukan $value (berisi teks nama elemen)
            $materiRow = MateriAjar::where('uuid', $this->uuid)
                ->where('user_id', (int)$this->selectedGuru)
                ->where('kelas_id', (int)$this->selectedKelas)
                ->where('mata_pelajaran_id', (int)$this->selectedMapel)
                ->where('semester_id', (int)$this->selectedSemester)
                ->where('elemen_pembelajaran', $value)
                ->first();

            if ($materiRow) {
                $materiArray = $materiRow->materi_ajar;
                if (is_string($materiArray)) {
                    $materiArray = json_decode($materiArray, true);
                }
                
                $filesArray = $materiRow->konten_file_path;
                if (is_string($filesArray)) {
                    $filesArray = json_decode($filesArray, true);
                }

                $formattedKonten = [];
                if (is_array($filesArray)) {
                    foreach ($filesArray as $filePath) {
                        $formattedKonten[] = [
                            'file_name' => basename($filePath),
                            'fullFilePath' => asset('storage/' . $filePath)
                        ];
                    }
                }

                $this->selectedElemenMateri = [
                    'semester' => $materiRow->semester,
                    'kategori_mata_pelajaran' => $materiRow->kategori_mata_pelajaran,
                    'elemen_pembelajaran' => $materiRow->elemen_pembelajaran,
                    'deskripsi_elemen_pembelajaran' => $materiRow->deskripsi_elemen_pembelajaran,
                    'materi' => is_array($materiArray) ? $materiArray : [],
                    'konten' => $formattedKonten
                ];
            }
        }
    }

    // ✨ TAMBAHAN: Aksi untuk memunculkan modal preview file dokumen/video
    public function openFileInModal($url, $fileName)
    {
        $this->modalContentUrl = $url;
        $this->modalFileName = $fileName;
        $this->showFullscreenModal = true;
    }

    public function closeFullscreenModal()
    {
        $this->showFullscreenModal = false;
        $this->reset(['modalContentUrl', 'modalFileName']);
    }

    public function render()
    {
        return view('livewire.read-data');
    }
}
