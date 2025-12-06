<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Periode;
use App\Models\EventSidang;

class EventSidangSeeder extends Seeder
{
    public function run(): void
    {
        // 2024-2025
        $p = Periode::where('nama', 'Semester Ganjil 2024/2025')->first();
        if ($p) {
            EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Ganjil 24/25 - Gel. 1', 'tipe' => 'SIDANG_TA']);
            EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Ganjil 24/25 - Gel. 2', 'tipe' => 'SIDANG_TA']);
        }
        $p = Periode::where('nama', 'Semester Genap 2024/2025')->first();
        if ($p) {
            EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Genap 24/25 - Gel. 1', 'tipe' => 'SIDANG_TA']);
            EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Genap 24/25 - Gel. 2', 'tipe' => 'SIDANG_TA']);
        }

        // 2025-2026 (Periode Aktif)
        $p = Periode::where('nama', 'Semester Ganjil 2025/2026')->first();
        if ($p) {
            EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Ganjil 25/26 - Gel. 1 (Nov)', 'tipe' => 'SIDANG_TA']);
            EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Ganjil 25/26 - Gel. 2 (Jan)', 'tipe' => 'SIDANG_TA']);
        }
        $p = Periode::where('nama', 'Semester Genap 2025/2026')->first();
        if ($p) {
            EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Genap 25/26 - Gel. 1', 'tipe' => 'SIDANG_TA']);
            EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Genap 25/26 - Gel. 2', 'tipe' => 'SIDANG_TA']);
        }

        // 2026-2027 Ganjil
        // Kita buat periodenya dulu karena seeder 2022-2026 belum mencakup ini
        $p = Periode::create([
            'nama' => 'Semester Ganjil 2026/2027',
            'tahun_akademik' => '2026/2027',
            'semester' => 'GANJIL',
            'is_active' => false,
        ]);
        EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Ganjil 26/27 - Gel. 1', 'tipe' => 'SIDANG_TA']);
        EventSidang::create(['periode_id' => $p->id, 'nama_event' => 'Sidang Ganjil 26/27 - Gel. 2', 'tipe' => 'SIDANG_TA']);
    }
}