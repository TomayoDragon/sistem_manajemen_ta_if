<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Staff;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\TugasAkhir;
class UserFactory extends Factory
{
    /**
     * Kata sandi default untuk semua user yang dibuat factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        // Ini adalah state default. 
        // Sebaiknya, user selalu dibuat dengan salah satu state peran di bawah.
        return [
            'login_id' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'mahasiswa_id' => null,
            'dosen_id' => null,
            'staff_id' => null,
            'admin_id' => null,
        ];
    }

    /**
     * Membuat state user sebagai MAHASISWA.
     */
/**
     * Membuat state user sebagai MAHASISWA.
     */
   public function mahasiswa(): Factory
    {
        return $this->state(function (array $attributes) {
            $mahasiswa = Mahasiswa::factory()->create();
            return [
                'mahasiswa_id' => $mahasiswa->id,
                'login_id' => $mahasiswa->nrp,
                'email' => $mahasiswa->nrp . '@student.ubaya.ac.id',
            ];
        });
    }

    /**
     * Menambahkan state untuk membuat Tugas Akhir.
     * HARUS dipanggil SETELAH state 'mahasiswa()'.
     */
   // ... (di dalam UserFactory.php)
    
    /**
     * Menambahkan state untuk membuat Tugas Akhir.
     * HARUS dipanggil SETELAH state 'mahasiswa()'.
     */
    public function withTugasAkhir($periodeId) // <-- TAMBAHKAN PARAMETER
    {
        return $this->afterCreating(function (User $user) use ($periodeId) {
            if ($user->mahasiswa_id) {
                $dosens = Dosen::inRandomOrder()->take(2)->pluck('id');
                if ($dosens->count() == 2) {
                    TugasAkhir::factory()->create([
                        'mahasiswa_id' => $user->mahasiswa_id,
                        'periode_id' => $periodeId, // <-- GUNAKAN PARAMETER
                        'dosen_pembimbing_1_id' => $dosens[0],
                        'dosen_pembimbing_2_id' => $dosens[1],
                        // Status default 'Bimbingan' dari factory sudah benar
                    ]);
                }
            }
        });
    }
    /**
     * Membuat state user sebagai DOSEN.
     */
    public function dosen(): Factory
    {
        return $this->state(function (array $attributes) {
            $dosen = Dosen::factory()->create();
            return [
                'dosen_id' => $dosen->id,
                'login_id' => $dosen->npk, // Login ID = NPK
                'email' => $dosen->npk . '@ubaya.ac.id', // Email dummy
            ];
        });
    }

    /**
     * Membuat state user sebagai STAFF.
     */
    public function staff(): Factory
    {
        return $this->state(function (array $attributes) {
            $staff = Staff::factory()->create();
            return [
                'staff_id' => $staff->id,
                'login_id' => $staff->npk, // Login ID = NPK
                'email' => $staff->npk . '@ubaya.ac.id', // Email dummy
            ];
        });
    }

    /**
     * Membuat state user sebagai ADMIN.
     */
    public function admin(): Factory
    {
        return $this->state(function (array $attributes) {
            $admin = Admin::factory()->create();
            return [
                'admin_id' => $admin->id,
                'login_id' => $admin->username, // Login ID = Username
                'email' => $admin->username . '@admin.sistem.id', // Email dummy
            ];
        });
    }
}