<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_pengajuans', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('pengajuan_sidang_id')
                  ->constrained('pengajuan_sidangs')
                  ->onDelete('cascade');
            
            // --- UPDATE ENUM DI SINI ---
            $table->enum('tipe_dokumen', [
                'NASKAH_TA',        // Folder (ZIP)
                'PROPOSAL_TA',      // Proposal + Bukti Penetapan Dosbing
                'ARTIKEL_JURNAL',   // Artikel Jurnal
                'KARTU_STUDI',      // Print out KS
                'SURAT_TUGAS',      // Surat Tugas TA
                'BUKTI_BIMBINGAN',  // Kartu & Bukti Bimbingan TA
                'SERTIFIKAT_LSTA',  // Sertifikat LSTA
                'BUKTI_DOSWAL',     // Bukti Bimbingan Doswal
                'VIDEO_PROMOSI'     // Video MP4
            ]);
            // ---------------------------
            
            $table->string('path_penyimpanan');
            $table->string('nama_file_asli');

            // Atribut Kriptografi (Tetap Ada)
            $table->string('hash_sha512_full', 128);
            $table->string('hash_blake2b_full', 128);
            $table->string('hash_combined', 128);
            $table->binary('signature_data');
            $table->boolean('is_signed')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_pengajuans');
    }
};