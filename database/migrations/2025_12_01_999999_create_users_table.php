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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Kolom login utama kita (NRP/NPK/Username)
            $table->string('login_id')->unique();
            
            // Kolom email untuk 'Lupa Password', boleh null
            $table->string('email')->unique()->nullable();
            
            // Kolom password bawaan Laravel
            $table->string('password');

            // Foreign Keys ke empat tabel peran
            // onDelete('set null') berarti jika profil (misal mahasiswa) dihapus,
            // akun user-nya tetap ada, tapi relasinya jadi null.
            $table->foreignId('mahasiswa_id')->nullable()->unique()
                  ->constrained('mahasiswas')->onDelete('set null');
                  
            $table->foreignId('dosen_id')->nullable()->unique()
                  ->constrained('dosens')->onDelete('set null');
                  
            $table->foreignId('staff_id')->nullable()->unique()
                  ->constrained('staff')->onDelete('set null');
                  
            $table->foreignId('admin_id')->nullable()->unique()
                  ->constrained('admins')->onDelete('set null');

            $table->rememberToken();
            $table->timestamps();
        });

        // Kita juga perlu migrasi ini untuk fitur 'Lupa Password'
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            // Kolom ini sekarang merujuk ke 'email', bukan 'login_id'
            // Ini adalah perilaku default Laravel yang kita ikuti
            $table->string('email')->primary(); 
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};