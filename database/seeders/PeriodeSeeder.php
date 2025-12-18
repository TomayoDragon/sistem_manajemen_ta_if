<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Periode;
use Carbon\Carbon;

class PeriodeSeeder extends Seeder
{
    public function run(): void
    {
        $years = [
            ['2022/2023', 2022],
            ['2023/2024', 2023],
            ['2024/2025', 2024],
            ['2025/2026', 2025], // <-- Kita set ini sebagai AKTIF nanti
            ['2026/2027', 2026],
        ];

        foreach ($years as $y) {
            $textYear = $y[0];
            $startYear = $y[1];

            // Semester Ganjil (Agustus - Januari)
            Periode::create([
                'nama' => "Semester Ganjil $textYear",
                'tanggal_mulai' => "$startYear-08-01",
                'tanggal_selesai' => ($startYear + 1) . "-01-31",
                'is_active' => ($textYear === '2025/2026'), // Set 2025/2026 Ganjil jadi AKTIF
            ]);

            // Semester Genap (Februari - Juli)
            Periode::create([
                'nama' => "Semester Genap $textYear",
                'tanggal_mulai' => ($startYear + 1) . "-02-01",
                'tanggal_selesai' => ($startYear + 1) . "-07-31",
                'is_active' => false,
            ]);
        }
    }
}