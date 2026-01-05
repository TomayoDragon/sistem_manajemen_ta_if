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
        Schema::create('dokumen_pengajuans', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('pengajuan_sidang_id')
                  ->constrained('pengajuan_sidangs')
                  ->onDelete('cascade');
            
            // --- DAFTAR TIPE DOKUMEN YANG DIPERBOLEHKAN ---
            // Pastikan SEMUA ini ada agar tidak error "Data truncated"
            $table->enum('tipe_dokumen', [
                'NASKAH_TA',
                'PROPOSAL_TA',
                'ARTIKEL_JURNAL',
                'KARTU_STUDI',
                'SURAT_TUGAS',
                'BUKTI_BIMBINGAN',  // <-- Pastikan ini ada!
                'SERTIFIKAT_LSTA',
                'BUKTI_PERSETUJUAN',
                'VIDEO_PROMOSI'
            ]);
            // ---------------------------------------------
            
            $table->string('path_penyimpanan');
            $table->string('nama_file_asli');

            // Kolom Kriptografi
            $table->string('hash_sha512_full', 128);
            $table->string('hash_blake2b_full', 128);
            $table->string('hash_combined', 128);
            $table->binary('signature_data');
            $table->boolean('is_signed')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_pengajuans');
    }
};