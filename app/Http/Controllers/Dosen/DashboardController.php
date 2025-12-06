<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TugasAkhir;
use App\Models\Lsta;
use App\Models\Sidang;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard dosen.
     */
    public function index()
    {
        // 1. Ambil data user dosen yang sedang login
        $dosen = Auth::user()->dosen;
        $dosenId = $dosen->id;

        // 2. HAPUS LOGIKA MAHASISWA BIMBINGAN DARI SINI

        // 3. Ambil jadwal LSTA (Logika ini tetap)
        $jadwalLsta = Lsta::where('status', 'TERJADWAL')
                            ->where(function ($query) use ($dosenId) {
                                $query->where('dosen_penguji_id', $dosenId)
                                      ->orWhereHas('tugasAkhir', function ($taQuery) use ($dosenId) {
                                          $taQuery->where('dosen_pembimbing_1_id', $dosenId)
                                                  ->orWhere('dosen_pembimbing_2_id', $dosenId);
                                      });
                            })
                            // Kita eager load relasi yang dibutuhkan untuk link verifikasi
                            ->with('tugasAkhir.mahasiswa', 'pengajuanSidang.dokumen')
                            ->orderBy('jadwal', 'asc')
                            ->get();

        // 4. Ambil jadwal Sidang (Logika ini tetap)
        $jadwalSidang = Sidang::where('status', 'TERJADWAL')
                              ->where(function ($query) use ($dosenId) {
                                  $query->where('dosen_penguji_ketua_id', $dosenId)
                                        ->orWhere('dosen_penguji_sekretaris_id', $dosenId)
                                        ->orWhereHas('tugasAkhir', function ($taQuery) use ($dosenId) {
                                            $taQuery->where('dosen_pembimbing_1_id', $dosenId)
                                                    ->orWhere('dosen_pembimbing_2_id', $dosenId);
                                        });
                              })
                              // Kita eager load relasi yang dibutuhkan untuk link verifikasi
                              ->with('tugasAkhir.mahasiswa', 'pengajuanSidang.dokumen')
                              ->orderBy('jadwal', 'asc')
                              ->get();

        // 5. Kirim data ke view (HANYA JADWAL)
        return view('dosen.dashboard', [
            'dosen' => $dosen,
            'jadwalLsta' => $jadwalLsta,
            'jadwalSidang' => $jadwalSidang,
            // Variabel 'mahasiswaBimbingan' DIHAPUS
        ]);
    }
}