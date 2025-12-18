<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::disableForeignKeyConstraints(); // Matikan cek FK sementara

        Schema::create('pengajuan_sidangs', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Tugas Akhir
            $table->foreignId('tugas_akhir_id')
                  ->constrained('tugas_akhirs')
                  ->onDelete('cascade');

            // Relasi ke Event Sidang
            // Pastikan tabel di database bernama 'events_sidangs' (jamak)
            $table->foreignId('event_sidang_id')
                  ->nullable()
                  ->constrained('events_sidangs')
                  ->onDelete('set null');

            $table->enum('status_validasi', ['PENDING', 'TERIMA', 'TOLAK'])->default('PENDING');
            $table->text('catatan_validasi')->nullable();
            
            // Relasi ke Staff
            $table->foreignId('validator_id')
                  ->nullable()
                  ->constrained('staff')
                  ->onDelete('set null');
                  
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_sidangs');
    }
};