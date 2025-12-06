<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
    {
        Schema::create('lstas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_akhir_id')->constrained('tugas_akhirs')->onDelete('cascade');
            $table->foreignId('pengajuan_sidang_id')->constrained('pengajuan_sidangs')->onDelete('cascade');
            $table->foreignId('event_sidang_id')->constrained('events_sidangs')->onDelete('cascade');
            $table->foreignId('dosen_penguji_id')->constrained('dosens')->onDelete('cascade');
            $table->dateTime('jadwal');
            $table->string('ruangan');
            
            // --- TAMBAHKAN KEMBALI BARIS INI ---
            $table->enum('status', ['TERJADWAL', 'LULUS', 'TIDAK_LULUS'])->default('TERJADWAL');
            
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('lstas');
    }
};