<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengajuan_sidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_akhir_id')->constrained('tugas_akhirs')->onDelete('cascade');

            // --- TAMBAHKAN BARIS INI ---
            // 'nullable' karena mahasiswa belum tentu masuk gelombang saat upload
            $table->foreignId('event_sidang_id')->nullable()->constrained('events_sidangs')->onDelete('set null');

            $table->enum('status_validasi', ['PENDING', 'TERIMA', 'TOLAK'])->default('PENDING');
            $table->text('catatan_validasi')->nullable();
            $table->foreignId('validator_id')->nullable()->constrained('staff')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_sidangs');
    }
};