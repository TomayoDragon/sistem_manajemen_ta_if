<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('tugas_akhirs', function (Blueprint $table) {
            // Menghubungkan TA ke Periode Semester
            // Dibuat nullable agar data lama tidak error
            $table->foreignId('periode_id')->nullable()->constrained('periodes')->onDelete('set null')->after('id');
        });
    }
    public function down(): void {
        Schema::table('tugas_akhirs', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
            $table->dropColumn('periode_id');
        });
    }
};