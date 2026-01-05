<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas_akhirs', function (Blueprint $table) {
            // Ubah menjadi NULLABLE
            $table->foreignId('dosen_pembimbing_2_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tugas_akhirs', function (Blueprint $table) {
            // Kembalikan ke NOT NULL (jika rollback)
            $table->foreignId('dosen_pembimbing_2_id')->nullable(false)->change();
        });
    }
};