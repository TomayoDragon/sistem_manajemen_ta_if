<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('mahasiswas', function (Blueprint $table) {
            // Public key (base64 encoded text)
            $table->text('public_key')->nullable()->after('nama_lengkap');

            // Private key (dienkripsi oleh Laravel Crypt)
            $table->text('private_key_encrypted')->nullable()->after('public_key');
        });
    }
    public function down(): void {
        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropColumn(['public_key', 'private_key_encrypted']);
        });
    }
};