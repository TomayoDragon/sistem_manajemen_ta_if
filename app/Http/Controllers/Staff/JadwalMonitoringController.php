<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lsta;
use App\Models\Sidang;

class JadwalMonitoringController extends Controller
{
    public function index()
    {
        // 1. Ambil Data LSTA (Load Relasi Pembimbing)
        $lstas = Lsta::with([
            'tugasAkhir.mahasiswa', 
            'tugasAkhir.dosenPembimbing1', // <--- Tambahan
            'tugasAkhir.dosenPembimbing2', // <--- Tambahan
            'dosenPenguji', 
            'eventSidang.periode'
        ])->whereHas('eventSidang.periode', function($q) {
            $q->where('is_active', true); 
        })->get();

        // 2. Ambil Data Sidang TA (Load Relasi Pembimbing)
        $sidangs = Sidang::with([
            'tugasAkhir.mahasiswa', 
            'tugasAkhir.dosenPembimbing1', // <--- Tambahan
            'tugasAkhir.dosenPembimbing2', // <--- Tambahan
            'dosenPengujiKetua', 
            'dosenPengujiSekretaris', 
            'eventSidang.periode'
        ])->whereHas('eventSidang.periode', function($q) {
            $q->where('is_active', true);
        })->get();

        // 3. Gabungkan Data
        $mergedJadwal = collect();

        foreach($lstas as $lsta) {
            $mergedJadwal->push([
                'tipe' => 'LSTA',
                'tanggal_raw' => $lsta->jadwal,
                'tanggal' => \Carbon\Carbon::parse($lsta->jadwal)->format('d M Y'),
                'jam' => \Carbon\Carbon::parse($lsta->jadwal)->format('H:i'),
                'ruangan' => $lsta->ruangan,
                'mahasiswa' => $lsta->tugasAkhir->mahasiswa->nama_lengkap . ' (' . $lsta->tugasAkhir->mahasiswa->nrp . ')',
                'judul' => $lsta->tugasAkhir->judul,
                
                // --- Tambahkan Data Pembimbing ---
                'pembimbing' => '<small>1.</small> ' . $lsta->tugasAkhir->dosenPembimbing1->nama_lengkap . '<br>' . 
                                '<small>2.</small> ' . $lsta->tugasAkhir->dosenPembimbing2->nama_lengkap,

                'penguji' => $lsta->dosenPenguji->nama_lengkap,
                'status' => $lsta->status,
            ]);
        }

        foreach($sidangs as $sidang) {
            $mergedJadwal->push([
                'tipe' => 'SIDANG TA',
                'tanggal_raw' => $sidang->jadwal,
                'tanggal' => \Carbon\Carbon::parse($sidang->jadwal)->format('d M Y'),
                'jam' => \Carbon\Carbon::parse($sidang->jadwal)->format('H:i'),
                'ruangan' => $sidang->ruangan,
                'mahasiswa' => $sidang->tugasAkhir->mahasiswa->nama_lengkap . ' (' . $sidang->tugasAkhir->mahasiswa->nrp . ')',
                'judul' => $sidang->tugasAkhir->judul,

                // --- Tambahkan Data Pembimbing ---
                'pembimbing' => '<small>1.</small> ' . $sidang->tugasAkhir->dosenPembimbing1->nama_lengkap . '<br>' . 
                                '<small>2.</small> ' . $sidang->tugasAkhir->dosenPembimbing2->nama_lengkap,

                'penguji' => 'Ketua: ' . $sidang->dosenPengujiKetua->nama_lengkap . '<br>Sek: ' . $sidang->dosenPengujiSekretaris->nama_lengkap,
                'status' => $sidang->status,
            ]);
        }

        $finalData = $mergedJadwal->sortBy('tanggal_raw');

        return view('staff.jadwal-monitoring', [
            'jadwals' => $finalData
        ]);
    }
}