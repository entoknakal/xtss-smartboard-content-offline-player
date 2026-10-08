<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriAjar extends Model
{
    protected $table = 'materi_ajars';
    protected $primaryKey = 'id';

    protected $fillable = [
        'uuid',
        'user_id',
        'kelas_id',
        'nama_kelas',
        'mata_pelajaran_id',
        'mata_pelajaran',
        'semester_id',
        'semester',
        'materi_ajar_id',
        'materi_ajar',
        'elemen_pembelajaran',
        'deskripsi_elemen_pembelajaran',
        'kategori_mata_pelajaran',
        'konten_file_path',
        'author',
    ];

    protected $casts = [
        'materi_ajar' => 'array',
        'konten_file_path' => 'array',
    ];

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
