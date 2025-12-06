<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mahasiswa;
use App\Models\Dosen;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TugasAkhir>
 */
class TugasAkhirFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Secara default, factory ini akan membuat mahasiswa & dosen baru
            // jika kita tidak menentukannya saat create().
            'mahasiswa_id' => Mahasiswa::factory(),
            'dosen_pembimbing_1_id' => Dosen::factory(),
            'dosen_pembimbing_2_id' => Dosen::factory(),

            'judul' => $this->faker->sentence(8), // Buat 8 kata judul acak
            
            // --- INI ADALAH PERBAIKANNYA ---
            // 'status' => $this->faker->randomElement(['Bimbingan', 'Menunggu Sidang', 'Revisi']), // <--- INI SALAH
            'status' => 'Bimbingan', // <--- INI YANG BENAR
            // --- AKHIR PERBAIKAN ---
        ];
    }
}