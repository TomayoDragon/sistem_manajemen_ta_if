<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tugas_akhirs', function (Blueprint $table) {
            // Ubah kolom menjadi nullable
            $table->unsignedBigInteger('dosen_pembimbing_2_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('tugas_akhirs', function (Blueprint $table) {
            // Kembalikan ke tidak nullable (jika rollback)
            // Hati-hati, ini bisa error jika ada data null.
            $table->unsignedBigInteger('dosen_pembimbing_2_id')->nullable(false)->change();
        });
    }
};