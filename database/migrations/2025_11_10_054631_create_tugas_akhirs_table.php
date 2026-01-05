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
        Schema::create('tugas_akhirs', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Mahasiswa
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
            
            $table->string('judul', 500);
            
            // Relasi ke Dosen Pembimbing 1 (WAJIB)
            $table->foreignId('dosen_pembimbing_1_id')->constrained('dosens')->onDelete('cascade');
            
            // Relasi ke Dosen Pembimbing 2 (BOLEH KOSONG / NULLABLE) - Poin 1
            $table->foreignId('dosen_pembimbing_2_id')->nullable()->constrained('dosens')->onDelete('cascade');

            // HAPUS kolom dosbing_1_approved_at & dosbing_2_approved_at (Jangan ditulis di sini) - Poin 2

            $table->enum('status', ['Bimbingan', 'Menunggu Sidang', 'Revisi', 'Selesai'])
                  ->default('Bimbingan');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_akhirs');
    }
};