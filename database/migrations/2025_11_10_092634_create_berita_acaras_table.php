<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('berita_acaras', function (Blueprint $table) {
            $table->id();

            // Relasi 1-to-1 dengan Sidang
            $table->foreignId('sidang_id')->unique()->constrained('sidangs')->onDelete('cascade');

            // --- Kolom Hasil Kalkulasi (Sekarang NOT NULL) ---
            $table->decimal('jumlah_nilai_mentah_nma', 6, 2);
            $table->decimal('rata_rata_nma', 5, 2);
            $table->string('nilai_relatif_nr', 2);
            $table->string('hasil_ujian'); // LULUS / TIDAK LULUS

            // --- Kolom Path PDF (HARUS TETAP NULLABLE) ---
            $table->string('path_file_generated')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('berita_acaras');
    }
};