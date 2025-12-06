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
        // Tabel ini untuk menyimpan "Gelombang Sidang"
        Schema::create('events_sidangs', function (Blueprint $table) {
            $table->id();
            
            // Terhubung ke periode semester
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            
            $table->string('nama_event'); // Contoh: "Sidang TA Ganjil - Gelombang 1 (November)"
            $table->enum('tipe', ['LSTA', 'SIDANG_TA']);
            $table->boolean('is_published')->default(false); // Penanda jadwal bisa dilihat mhs
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