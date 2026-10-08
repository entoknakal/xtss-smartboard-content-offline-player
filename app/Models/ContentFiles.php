<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ContentFiles extends Model
{
    protected $table = 'content_files';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'file_path',
        'data_json',
        'file_size_bytes',
    ];

    protected $casts = [
        'data_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mengubah struktur JSON dinamis menjadi Array Bersih untuk Filament
     */
    protected function daftarKelas(): Attribute
    {
        return Attribute::make(
            get: function () {
                $data = $this->data_json;

                if (empty($data) || !is_array($data)) {
                    return [];
                }

                // 1. Ambil UUID (key terluar dari objek data_json)
                $uuid = (string) array_key_first($data);

                $firstKeyData = reset($data);

                // 2. Ambil user_id dari dalam JSON data
                $jsonUserId = $firstKeyData['user_id'] ?? '-';

                $kelasData = $firstKeyData['kelas'] ?? [];
                $formattedKelas = [];

                foreach ($kelasData as $kelas) {
                    $kelasId = $kelas['kelas_id'] ?? '-';
                    $rawMateriAjar = $kelas['materi_ajar'] ?? [];
                    $groupedSemester = [];

                    foreach ($rawMateriAjar as $materi) {
                        $semesterName = $materi['semester'] ?? 'Tanpa Semester';
                        $mapelName = $materi['mata_pelajaran'] ?? 'Tanpa Mata Pelajaran';

                        $formattedFilePaths = [];
                        $rawFilePaths = $materi['konten_file_path'] ?? [];

                        foreach ($rawFilePaths as $filePath) {
                            // Ambil nama file asli (misal: "01KPZ90S7TE4JREP28QFX3JK0N.jpg")
                            $baseName = basename($filePath);

                            $fullPathString = "{$uuid}/{$jsonUserId}/{$kelasId}/{$baseName}";

                            // Gabungkan menjadi: [uuid]/[user_id]/[kelas_id]/basename
                            // $formattedFilePaths[] = "{$uuid}/{$jsonUserId}/{$kelasId}/{$baseName}";
                            $formattedFilePaths[] = [
                                'path_lengkap' => $fullPathString,
                            ];
                        }

                        // 1. Inisialisasi Group Semester
                        if (!isset($groupedSemester[$semesterName])) {
                            $groupedSemester[$semesterName] = [
                                'nama_semester' => $semesterName,
                                'daftar_mapel' => [], // Menampung kumpulan mata pelajaran di semester ini
                            ];
                        }

                        // 2. Inisialisasi Group Mata Pelajaran di dalam Semester terkait
                        if (!isset($groupedSemester[$semesterName]['daftar_mapel'][$mapelName])) {
                            $groupedSemester[$semesterName]['daftar_mapel'][$mapelName] = [
                                'nama_mata_pelajaran' => $mapelName,
                                'detail_elemen' => [], // Menampung materi/elemen dari mata pelajaran ini
                            ];
                        }

                        // 3. Masukkan detail elemen pembelajaran, bab (materi), dan file
                        $groupedSemester[$semesterName]['daftar_mapel'][$mapelName]['detail_elemen'][] = [
                            'elemen_pembelajaran' => $materi['elemen_pembelajaran'] ?? '-',
                            'deskripsi_elemen_pembelajaran' => $materi['deskripsi_elemen_pembelajaran'] ?? '-',
                            'author' => $materi['author'] ?? '-',
                            'materi' => $materi['materi'] ?? [],
                            // 'konten_file_path' => $materi['konten_file_path'] ?? [],
                            'konten_file_path' => $formattedFilePaths,
                        ];
                    }

                    // Ubah associative array menjadi indexed murni agar ramah terhadap RepeatableEntry Filament
                    $formattedMateriAjar = [];
                    foreach ($groupedSemester as $semKey => $semValue) {
                        $formattedMateriAjar[] = [
                            'nama_semester' => $semValue['nama_semester'],
                            'daftar_mapel' => array_values($semValue['daftar_mapel']),
                        ];
                    }

                    $formattedKelas[] = [
                        'kelas_id' => $kelas['kelas_id'] ?? null,
                        'nama_kelas' => $kelas['nama_kelas'] ?? '-',
                        'materi_ajar' => $formattedMateriAjar,
                    ];
                }
                // dd($formattedKelas);
                return $formattedKelas;
            }
        );
    }
    //     protected function daftarKelas(): Attribute
    //     {
    //         return Attribute::make(
    //             get: function () {
    //                 $data = $this->data_json;

    //                 if (empty($data) || !is_array($data)) {
    //                     return [];
    //                 }

    //                 $firstKeyData = reset($data);
    //                 $kelasData = $firstKeyData['kelas'] ?? [];

    //                 $formattedKelas = [];

    //                 foreach ($kelasData as $kelas) {
    //                     $rawMateriAjar = $kelas['materi_ajar'] ?? [];
    //                     $groupedMateriAjar = [];

    //                     // Proses pengelompokkan materi_ajar berdasarkan nama/id semester
    //                     foreach ($rawMateriAjar as $materi) {
    //                         $semesterName = $materi['semester'] ?? 'Tanpa Semester';

    //                         // Jika semester belum ada di group, inisialisasi strukturnya
    //                         if (!isset($groupedMateriAjar[$semesterName])) {
    //                             $groupedMateriAjar[$semesterName] = [
    //                                 'nama_semester' => $semesterName,
    //                                 'detail_materi' => [], // Menampung kumpulan mata pelajaran di semester ini
    //                             ];
    //                         }

    //                         // Masukkan detail mata pelajaran, elemen, dan materi (bab) ke dalam semester terkait
    //                         $groupedMateriAjar[$semesterName]['detail_materi'][] = [
    //                             'mata_pelajaran' => $materi['mata_pelajaran'] ?? '-',
    //                             'elemen_pembelajaran' => $materi['elemen_pembelajaran'] ?? '-',
    //                             'deskripsi_elemen_pembelajaran' => $materi['deskripsi_elemen_pembelajaran'] ?? '-',
    //                             'author' => $materi['author'] ?? '-',
    //                             'materi' => $materi['materi'] ?? [], // Array berisi nama_bab, tujuan_pembelajaran, alokasi_waktu
    //                             'konten_file_path' => $materi['konten_file_path'] ?? [], // Menyertakan kontent_file_path permintaan Anda
    //                         ];
    //                     }

    //                     $formattedKelas[] = [
    //                         'kelas_id' => $kelas['kelas_id'] ?? null,
    //                         'nama_kelas' => $kelas['nama_kelas'] ?? '-',
    //                         // Ubah menjadi indexed array murni agar bisa dibaca RepeatableEntry
    //                         'materi_ajar' => array_values($groupedMateriAjar), 
    //                     ];
    //                 }
    // dd($formattedKelas);
    //                 return $formattedKelas;
    //             }
    //         );
    //     }
    // protected function daftarKelas(): Attribute
    // {
    //     return Attribute::make(
    //         get: function () {
    //             $data = $this->data_json; // Ambil kolom data_json

    //             if (empty($data) || !is_array($data)) {
    //                 return [];
    //             }

    //             // 1. Ambil isi dari UUID pertama (karena key UUID terluar dinamis)
    //             $firstKeyData = reset($data);

    //             // 2. Ambil objek 'kelas' di dalamnya
    //             $kelasData = $firstKeyData['kelas'] ?? [];

    //             // 3. Karena key ID kelas juga dinamis ("3", "2"), ubah menjadi indexed array murni
    //             return array_values($kelasData);
    //         }
    //     );
    // }
}
