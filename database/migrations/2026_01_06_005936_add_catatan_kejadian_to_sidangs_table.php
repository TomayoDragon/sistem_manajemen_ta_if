<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sidangs', function (Blueprint $table) {
            // Menambahkan kolom teks, nullable karena tidak selalu ada kejadian
            $table->text('catatan_kejadian')->nullable()->after('ruangan');
        });
    }

    public function down()
    {
        Schema::table('sidangs', function (Blueprint $table) {
            $table->dropColumn('catatan_kejadian');
        });
    }
};