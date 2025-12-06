<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('dokumen_hasil_sidangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sidang_id')->constrained('sidangs')->onDelete('cascade');
            // Enum untuk membedakan apakah ini Revisi atau BA (jika nanti BA mau didigitalkan juga)
            $table->enum('jenis_dokumen', ['LEMBAR_REVISI', 'BERITA_ACARA']);
            
            // Lokasi File Fisik & Nama Asli
            $table->string('path_file');
            $table->string('nama_file_asli');

            // Data Integritas (Hashing - Meniru struktur UploadController kamu)
            $table->string('hash_sha512_full');
            $table->string('hash_blake2b_full');
            $table->string('hash_combined');

            // Digital Signature (System Key)
            $table->text('signature_data'); // Signature panjang, pakai text
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dokumen_hasil_sidangs');
    }
};