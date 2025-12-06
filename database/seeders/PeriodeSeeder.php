<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Periode;

class PeriodeSeeder extends Seeder
{
    public function run(): void
    {
        Periode::create([
            'nama' => 'Semester Ganjil 2022/2023',
            'tahun_akademik' => '2022/2023',
            'semester' => 'GANJIL',
            'is_active' => false,
        ]);
        Periode::create([
            'nama' => 'Semester Genap 2022/2023',
            'tahun_akademik' => '2022/2023',
            'semester' => 'GENAP',
            'is_active' => false,
        ]);
        
        Periode::create([
            'nama' => 'Semester Ganjil 2023/2024',
            'tahun_akademik' => '2023/2024',
            'semester' => 'GANJIL',
            'is_active' => false,
        ]);
        Periode::create([
            'nama' => 'Semester Genap 2023/2024',
            'tahun_akademik' => '2023/2024',
            'semester' => 'GENAP',
            'is_active' => false,
        ]);

        Periode::create([
            'nama' => 'Semester Ganjil 2024/2025',
            'tahun_akademik' => '2024/2025',
            'semester' => 'GANJIL',
            'is_active' => false,
        ]);
        Periode::create([
            'nama' => 'Semester Genap 2024/2025',
            'tahun_akademik' => '2024/2025',
            'semester' => 'GENAP',
            'is_active' => false,
        ]);

        // INI PERIODE AKTIF KITA (ASUMSI)
        Periode::create([
            'nama' => 'Semester Ganjil 2025/2026',
            'tahun_akademik' => '2025/2026',
            'semester' => 'GANJIL',
            'is_active' => true,
        ]);
        Periode::create([
            'nama' => 'Semester Genap 2025/2026',
            'tahun_akademik' => '2025/2026',
            'semester' => 'GENAP',
            'is_active' => false,
        ]);
    }
}