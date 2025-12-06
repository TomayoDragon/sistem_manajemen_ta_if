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
        // Tabel ini untuk menyimpan data master semester
        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Contoh: "Semester Ganjil 2025/2026"
            $table->string('tahun_akademik'); // Contoh: "2025/2026"
            $table->enum('semester', ['GANJIL', 'GENAP']);
            $table->boolean('is_active')->default(false); // Penanda periode aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodes');
    }
};