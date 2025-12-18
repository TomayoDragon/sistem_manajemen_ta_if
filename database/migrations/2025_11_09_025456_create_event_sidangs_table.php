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
        Schema::create('events_sidangs', function (Blueprint $table) {
            $table->id();

            // Relasi ke Periode
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');

            $table->string('nama_event');
            $table->enum('tipe', ['LSTA', 'SIDANG_TA']);
            $table->boolean('is_published')->default(false);

            // --- INI KOLOM YANG HILANG DAN PERLU DITAMBAHKAN ---
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            // ---------------------------------------------------

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events_sidangs');
    }
};