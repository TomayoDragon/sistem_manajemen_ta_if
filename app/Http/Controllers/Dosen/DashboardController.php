<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lsta;
use App\Models\Sidang;

class DashboardController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;
        $dosenId = $dosen->id;

        // --- 1. JADWAL MENGUJI LSTA (PERBAIKAN STRICT) ---
        // HANYA jika dosen ID tercatat sebagai 'dosen_penguji_id'.
        // Pembimbing TIDAK akan melihat ini kecuali dia juga pengujinya.
        $jadwalLsta = Lsta::with(['tugasAkhir.mahasiswa', 'pengajuanSidang.dokumen'])
            ->where('dosen_penguji_id', $dosenId) 
            ->where('status', 'TERJADWAL') // Pastikan statusnya valid
            ->orderBy('jadwal', 'asc')
            ->get();

        // --- 2. JADWAL MENGUJI SIDANG TA (Logika Tetap) ---
        // Muncul jika Ketua, Sekretaris, atau Pembimbing 1/2
        $jadwalSidang = Sidang::with(['tugasAkhir.mahasiswa', 'pengajuanSidang.dokumen'])
            ->where('status', 'TERJADWAL')
            ->where(function ($query) use ($dosenId) {
                $query->where('dosen_penguji_ketua_id', $dosenId)
                      ->orWhere('dosen_penguji_sekretaris_id', $dosenId)
                      ->orWhereHas('tugasAkhir', function ($q) use ($dosenId) {
                          $q->where('dosen_pembimbing_1_id', $dosenId)
                            ->orWhere('dosen_pembimbing_2_id', $dosenId);
                      });
            })
            ->orderBy('jadwal', 'asc')
            ->get();

        return view('dosen.dashboard', [
            'dosen' => $dosen,
            'jadwalLsta' => $jadwalLsta,
            'jadwalSidang' => $jadwalSidang
        ]);
    }
}