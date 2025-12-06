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
        Schema::table('tugas_akhirs', function (Blueprint $table) {
            // Kolom untuk persetujuan Dosbing 1
            $table->timestamp('dosbing_1_approved_at')->nullable()->after('status');
            // Kolom untuk persetujuan Dosbing 2
            $table->timestamp('dosbing_2_approved_at')->nullable()->after('dosbing_1_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_akhirs', function (Blueprint $table) {
            $table->dropColumn(['dosbing_1_approved_at', 'dosbing_2_approved_at']);
        });
    }
};