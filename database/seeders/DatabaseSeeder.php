<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Dosen;
use App\Models\TugasAkhir;
use App\Models\Staff;      // <-- Import Staff
use App\Models\Admin;      // <-- Import Admin
use App\Models\Periode;    // <-- Import Periode (BARU)
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ==========================================================
        // LANGKAH 0: JALANKAN SEEDER MASTER (PERIODE & EVENT)
        // ==========================================================
        $this->call([
            PeriodeSeeder::class,
            EventSidangSeeder::class,
        ]);

        // 2. Ambil Periode Aktif (2025/2026 Ganjil)
        $activePeriode = Periode::where('is_active', true)->first();

        // Fallback jika seeder periode belum diset
        if (!$activePeriode) {
            $activePeriode = Periode::firstOrCreate([
                'nama' => 'Semester Ganjil 2025/2026',
                'tahun_akademik' => '2025/2026',
                'semester' => 'GANJIL',
                'is_active' => true,
            ]);
        }

        // ==========================================================
        // LANGKAH 1: BUAT SEMUA DOSEN TERLEBIH DAHULU
        // ==========================================================

        // Buat Dosen Joko (Dosbing 1 James)
        $dosen_joko = Dosen::factory()->create([
            'npk' => '12345678',
            'nama_lengkap' => 'Dr. Joko Siswantoro',
        ]);
        User::factory()->create([
            'dosen_id' => $dosen_joko->id,
            'login_id' => $dosen_joko->npk,
            'email' => 'joko@ubaya.ac.id',
            'password' => Hash::make('password123'), // Ganti password agar tidak sama
        ]);

        // Buat Dosen Ahmad (Dosbing 2 James)
        $dosen_ahmad = Dosen::factory()->create([
            'npk' => '11223344',
            'nama_lengkap' => 'Ahmad Miftah Fajrin, M.Kom.',
        ]);
        User::factory()->create([
            'dosen_id' => $dosen_ahmad->id,
            'login_id' => $dosen_ahmad->npk,
            'email' => 'ahmad@ubaya.ac.id',
            'password' => Hash::make('password123'), // Ganti password agar tidak sama
        ]);

        // Buat 2 user dosen random (agar ada total 4 dosen di DB)
        User::factory(5)->dosen()->create();


        // ==========================================================
        // LANGKAH 2: BUAT MAHASISWA & ROLE LAIN
        // ==========================================================

        // Buat Mahasiswa James
        $mhs_james = \App\Models\Mahasiswa::factory()->create([
            'nrp' => '160422100',
            'nama_lengkap' => 'James Dharmawan',
        ]);
        User::factory()->create([
            'mahasiswa_id' => $mhs_james->id,
            'login_id' => $mhs_james->nrp,
            'email' => '160422100@student.ubaya.ac.id', // Sesuaikan dengan NRP
            'password' => Hash::make('password123'),
        ]);

        // Buat Staff Duladi
        $staff_duladi = Staff::factory()->create([
            'npk' => '87654321',
            'nama_lengkap' => 'Duladi',
        ]);
        User::factory()->create([
            'staff_id' => $staff_duladi->id,
            'login_id' => $staff_duladi->npk,
            'email' => 'duladi@ubaya.ac.id',
            'password' => Hash::make('password123'), // Ganti password
        ]);

        // Buat Admin
        $admin_super = Admin::factory()->create([
            'username' => 'admin',
            'nama_lengkap' => 'Super Admin',
        ]);
        User::factory()->create([
            'admin_id' => $admin_super->id,
            'login_id' => $admin_super->username,
            'email' => 'admin@sistem.id',
            'password' => Hash::make('admin123'),
        ]);


        // ==========================================================
        // LANGKAH 3: BUAT DATA TUGAS AKHIR
        // ==========================================================

        // Buat TA spesifik untuk James (Hubungkan ke Periode Aktif)
        TugasAkhir::factory()->create([
            'mahasiswa_id' => $mhs_james->id,
            'periode_id' => $activePeriode->id, // <--- PENTING!
            'judul' => 'Pembuatan Sistem Manajemen Berkas Tugas Akhir Dengan Digital Signature',
            'dosen_pembimbing_1_id' => $dosen_joko->id,
            'dosen_pembimbing_2_id' => $dosen_ahmad->id,
            'status' => 'Bimbingan',
            'dosbing_1_approved_at' => now(),
            'dosbing_2_approved_at' => now(),
        ]);
        // Buat 5 user mhs DENGAN TA (Hubungkan ke Periode Aktif)
        User::factory(5)->mahasiswa()->withTugasAkhir($activePeriode->id)->create(); // <-- PERUBAHAN DI SINI

        // Buat 5 user mhs TANPA TA
        User::factory(5)->mahasiswa()->create();
    }
}