<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('dokumen_hasil_sidangs', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada (untuk menghindari error duplicate column jika di-run ulang)
            if (!Schema::hasColumn('dokumen_hasil_sidangs', 'dosen_id')) {
                // Tambahkan kolom dosen_id setelah sidang_id
                $table->unsignedBigInteger('dosen_id')->nullable()->after('sidang_id');

                // Tambahkan foreign key
                $table->foreign('dosen_id')->references('id')->on('dosens')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('dokumen_hasil_sidangs', function (Blueprint $table) {
            // Hapus FK dan Kolom saat rollback
            // Gunakan array untuk dropForeign (Laravel otomatis mencari nama constraintnya)
            $table->dropForeign(['dosen_id']); 
            $table->dropColumn('dosen_id');
        });
    }
};