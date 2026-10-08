<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('materi_ajars', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignIdFor(User::class, 'user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('kelas_id');
            $table->string('nama_kelas');
            $table->unsignedBigInteger('mata_pelajaran_id');
            $table->string('mata_pelajaran');
            $table->unsignedBigInteger('semester_id');
            $table->string('semester');
            $table->unsignedBigInteger('materi_ajar_id')->comment('materi_ajar_id = elemen_pembelajaran_id');
            $table->json('materi_ajar');
            $table->string('elemen_pembelajaran');
            $table->longText('deskripsi_elemen_pembelajaran');
            $table->string('kategori_mata_pelajaran');
            $table->string('konten_file_path');
            $table->string('author');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_ajars');
    }
};
