<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TugasAkhir;
use App\Models\Periode;

class ArsipController extends Controller
{
    /**
     * Menampilkan halaman Arsip TA dengan fitur PENCARIAN dan FILTER PERIODE.
     */
    public function index(Request $request)
    {
        // 1. Ambil data dari request (search & filter)
        $searchQuery = $request->search;
        $selectedPeriodeId = $request->periode_id;

        // 2. Ambil SEMUA periode untuk mengisi dropdown
        // PERBAIKAN: Urutkan berdasarkan 'tanggal_mulai' (karena tahun_akademik sudah dihapus)
        $periodes = Periode::orderBy('tanggal_mulai', 'desc')->get();

        // 3. Query utama untuk mengambil data TA
        $query = TugasAkhir::query()
                            ->with('mahasiswa', 'dosenPembimbing1', 'dosenPembimbing2', 'periode')
                            ->orderBy('created_at', 'desc');

        // 4. Terapkan Filter Pencarian (jika ada)
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('judul', 'like', '%' . $searchQuery . '%')
                  ->orWhereHas('mahasiswa', function ($mahasiswaQuery) use ($searchQuery) {
                      $mahasiswaQuery->where('nama_lengkap', 'like', '%' . $searchQuery . '%')
                                     ->orWhere('nrp', 'like', '%' . $searchQuery . '%');
                  });
            });
        }
        
        // 5. Terapkan Filter Periode (jika ada)
        if ($selectedPeriodeId) {
            $query->where('periode_id', $selectedPeriodeId);
        }
        
        // 6. Ambil data dengan pagination
        $arsipTugasAkhir = $query->paginate(20)->withQueryString();

        // 7. Kirim semua data ke view
        return view('staff.arsip-index', [
            'arsipTugasAkhir' => $arsipTugasAkhir,
            'periodes' => $periodes,
            'searchQuery' => $searchQuery,
            'selectedPeriodeId' => $selectedPeriodeId,
        ]);
    }

    /**
     * Menampilkan detail TA
     */
    public function show(TugasAkhir $tugasAkhir)
    {
        $tugasAkhir->load(
            'mahasiswa', 
            'dosenPembimbing1', 
            'dosenPembimbing2', 
            'lstas', 
            'sidangs.beritaAcara', // Load BA untuk di-download staf
            'pengajuanSidangs.dokumen', 
            'pengajuanSidangs.validator',
            'periode'
        );

        return view('staff.arsip-detail', [
            'ta' => $tugasAkhir
        ]);
    }
}