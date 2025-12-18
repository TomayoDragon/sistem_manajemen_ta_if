<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Periode;
use App\Models\EventSidang;
use Carbon\Carbon;

class EventSidangSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil periode dari 2024 ke atas
        $periodes = Periode::where('tanggal_mulai', '>=', '2024-01-01')->get();

        foreach ($periodes as $p) {
            // Tentukan tahun dari nama periode untuk label
            $tahun = substr($p->nama, -9); // Ambil "202X/202X"

            // Gelombang 1 (Bulan ke-3 periode)
            EventSidang::create([
                'periode_id' => $p->id,
                'nama_event' => "Sidang TA Gel. 1 ({$p->nama})",
                'tipe' => 'SIDANG_TA',
                'is_published' => true,
                'tanggal_mulai' => Carbon::parse($p->tanggal_mulai)->addMonths(2),
                'tanggal_selesai' => Carbon::parse($p->tanggal_mulai)->addMonths(2)->addDays(14),
            ]);

            // Gelombang 2 (Bulan ke-5 periode)
            EventSidang::create([
                'periode_id' => $p->id,
                'nama_event' => "Sidang TA Gel. 2 ({$p->nama})",
                'tipe' => 'SIDANG_TA',
                'is_published' => true,
                'tanggal_mulai' => Carbon::parse($p->tanggal_mulai)->addMonths(4),
                'tanggal_selesai' => Carbon::parse($p->tanggal_mulai)->addMonths(4)->addDays(14),
            ]);
        }
    }
}