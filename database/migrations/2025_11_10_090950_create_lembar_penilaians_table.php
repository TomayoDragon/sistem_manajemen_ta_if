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
        Schema::create('lembar_penilaians', function (Blueprint $table) {
            $table->id();

            // Kolom untuk Dosen yang memberi nilai
            $table->foreignId('dosen_id')->constrained('dosens')->onDelete('cascade');

            // --- Relasi Polimorfik ---
            // 'penilaian_type' akan menyimpan (App\Models\Lsta atau App\Models\Sidang)
            // 'penilaian_id' akan menyimpan (ID dari LSTA atau Sidang)
            $table->morphs('penilaian');

            // --- 5 Komponen Nilai (dari Gambar 3.3) ---
            $table->tinyInteger('nilai_materi')->default(0);
            $table->tinyInteger('nilai_sistematika')->default(0);
            $table->tinyInteger('nilai_mempertahankan')->default(0);
            $table->tinyInteger('nilai_pengetahuan_bidang')->default(0);
            $table->tinyInteger('nilai_karya_ilmiah')->default(0);

            $table->text('komentar_revisi')->nullable();
            
            // Pastikan 1 Dosen hanya bisa menilai 1 event 1x
            $table->unique(['dosen_id', 'penilaian_type', 'penilaian_id'], 'penilaian_dosen_unik');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lembar_penilaians');
    }
};