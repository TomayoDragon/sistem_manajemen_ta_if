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
            
            // Terhubung ke "Paket" pengajuan
            $table->foreignId('pengajuan_sidang_id')->constrained('pengajuan_sidangs')->onDelete('cascade');
            
            // Tipe file (salah satu dari 3)
            $table->enum('tipe_dokumen', ['BUKU_SKRIPSI', 'KHS', 'TRANSKRIP']);
            
            // Info file
            $table->string('path_penyimpanan');
            $table->string('nama_file_asli');

            // --- Kolom untuk Novelti Skripsi Anda (Per File) ---
            $table->string('hash_sha512_full', 128);
            $table->string('hash_blake2b_full', 128); // Tetap pakai nama ini
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