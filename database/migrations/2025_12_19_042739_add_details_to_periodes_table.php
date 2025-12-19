<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodes', function (Blueprint $table) {
            // Menambahkan kolom yang kurang agar sesuai dengan Model Periode.php
            $table->string('tahun_akademik', 20)->nullable()->after('tanggal_selesai'); // Misal: "2025/2026"
            $table->enum('semester', ['GANJIL', 'GENAP', 'PENDEK'])->nullable()->after('tahun_akademik');
        });
    }

    public function down(): void
    {
        Schema::table('periodes', function (Blueprint $table) {
            $table->dropColumn(['tahun_akademik', 'semester']);
        });
    }
};