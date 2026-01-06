<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('detail_revisis', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Lembar Penilaian (Punya Dosen Siapa)
            $table->foreignId('lembar_penilaian_id')
                  ->constrained('lembar_penilaians')
                  ->onDelete('cascade'); // Jika nilai dihapus, detail revisi ikut hilang
            
            // Uraian Revisi (Diisi Dosen)
            $table->text('isi_revisi'); 
            
            // Keterangan/Tanggapan (Diisi Mahasiswa) - Nullable karena awal dibuat pasti kosong
            $table->text('keterangan_mahasiswa')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('detail_revisis');
    }
};