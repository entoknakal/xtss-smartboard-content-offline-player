<?php

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
        Schema::table('materi_ajars', function (Blueprint $table) {
            $table->unique(
                ['uuid', 'user_id', 'kelas_id', 'mata_pelajaran_id', 'semester_id', 'materi_ajar_id'],
                'materi_ajars_composite_unique' // Nama index (bebas)
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materi_ajars', function (Blueprint $table) {
            $table->dropUnique('materi_ajars_composite_unique');
        });
    }
};
