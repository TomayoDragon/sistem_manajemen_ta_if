<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\TugasAkhir;
use App\Models\Staff;
use App\Models\Admin;
use App\Models\Periode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================================
        // 0. SETUP PERIODE
        // ==========================================================
        $activePeriode = Periode::firstOrCreate(
            ['nama' => 'Semester Ganjil 2025/2026'], 
            [
                'tanggal_mulai' => '2025-08-01',
                'tanggal_selesai' => '2026-01-31',
                'is_active' => true,
            ]
        );

        // ==========================================================
        // 1. DATA UTAMA (Manual & Pasti Benar)
        // ==========================================================
        
        // --- Dosen ---
        $dosen_joko = Dosen::create(['npk' => '12345678', 'nama_lengkap' => 'Dr. Joko Siswantoro', 'gelar_depan' => 'Dr.']);
        User::create(['login_id' => $dosen_joko->npk, 'email' => 'joko@ubaya.ac.id', 'password' => Hash::make('password123'), 'dosen_id' => $dosen_joko->id]);

        $dosen_ahmad = Dosen::create(['npk' => '11223344', 'nama_lengkap' => 'Ahmad Miftah Fajrin', 'gelar_belakang' => 'M.Kom.']);
        User::create(['login_id' => $dosen_ahmad->npk, 'email' => 'ahmad@ubaya.ac.id', 'password' => Hash::make('password123'), 'dosen_id' => $dosen_ahmad->id]);

        // --- Mahasiswa ---
        $mhs_james = Mahasiswa::create(['nrp' => '160422100', 'nama_lengkap' => 'James Dharmawan']);
        User::create(['login_id' => $mhs_james->nrp, 'email' => '160422100@student.ubaya.ac.id', 'password' => Hash::make('password123'), 'mahasiswa_id' => $mhs_james->id]);

        $mhs_budi = Mahasiswa::create(['nrp' => '160422001', 'nama_lengkap' => 'Budi Santoso']);
        User::create(['login_id' => $mhs_budi->nrp, 'email' => 'budi@student.ubaya.ac.id', 'password' => Hash::make('password123'), 'mahasiswa_id' => $mhs_budi->id]);

        // --- Staff & Admin ---
        $staff = Staff::create(['npk' => '87654321', 'nama_lengkap' => 'Duladi']);
        User::create(['login_id' => $staff->npk, 'email' => 'duladi@ubaya.ac.id', 'password' => Hash::make('password123'), 'staff_id' => $staff->id]);

        $admin = Admin::create(['username' => 'admin', 'nama_lengkap' => 'Super Admin']);
        User::create(['login_id' => $admin->username, 'email' => 'admin@sistem.id', 'password' => Hash::make('admin123'), 'admin_id' => $admin->id]);

        // --- Tugas Akhir ---
        TugasAkhir::create([
            'mahasiswa_id' => $mhs_james->id, 'periode_id' => $activePeriode->id, 'judul' => 'Sistem Digital Signature',
            'dosen_pembimbing_1_id' => $dosen_joko->id, 'dosen_pembimbing_2_id' => $dosen_ahmad->id, 
            'status' => 'Bimbingan' // STATUS VALID
        ]);

        TugasAkhir::create([
            'mahasiswa_id' => $mhs_budi->id, 'periode_id' => $activePeriode->id, 'judul' => 'Analisis Algoritma',
            'dosen_pembimbing_1_id' => $dosen_joko->id, 'dosen_pembimbing_2_id' => null, 
            'status' => 'Bimbingan' // STATUS VALID
        ]);


        // ==========================================================
        // 2. DATA DUMMY TAMBAHAN
        // ==========================================================

        // --- 15 Dosen Dummy ---
        for ($i = 1; $i <= 15; $i++) {
            $npkDummy = '99900' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $dosen = Dosen::create(['npk' => $npkDummy, 'nama_lengkap' => 'Dosen Dummy ' . $i]);
            User::create(['login_id' => $npkDummy, 'email' => 'dosen' . $i . '@dummy.com', 'password' => Hash::make('password'), 'dosen_id' => $dosen->id]);
        }

        // --- 20 Mahasiswa Dummy ---
        for ($i = 1; $i <= 20; $i++) {
            $nrpDummy = '160499' . str_pad($i, 3, '0', STR_PAD_LEFT);
            
            $mhs = Mahasiswa::create(['nrp' => $nrpDummy, 'nama_lengkap' => 'Mahasiswa Dummy ' . $i]);
            User::create(['login_id' => $nrpDummy, 'email' => $nrpDummy . '@student.dummy.com', 'password' => Hash::make('password'), 'mahasiswa_id' => $mhs->id]);

            // Buat TA
            $hasDosbing2 = ($i % 2 == 0); 

            TugasAkhir::create([
                'mahasiswa_id' => $mhs->id,
                'periode_id' => $activePeriode->id,
                'judul' => 'Judul Skripsi Dummy Nomor ' . $i,
                'dosen_pembimbing_1_id' => $dosen_joko->id, 
                'dosen_pembimbing_2_id' => $hasDosbing2 ? $dosen_ahmad->id : null,
                'status' => 'Bimbingan', 
            ]);
        }
    }
}