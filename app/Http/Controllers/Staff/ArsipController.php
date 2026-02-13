<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TugasAkhir;
use App\Models\Periode;

class ArsipController extends Controller
{
    /**
     * Menampilkan halaman Arsip TA dengan optimasi DataTables.
     */
    public function index(Request $request)
    {
        $selectedPeriodeId = $request->periode_id;

        // 1. Ambil SEMUA periode untuk dropdown filter
        $periodes = Periode::orderBy('tanggal_mulai', 'desc')->get();

        // 2. Query Utama
        $query = TugasAkhir::query()
                    ->with(['mahasiswa', 'dosenPembimbing1', 'dosenPembimbing2', 'periode'])
                    ->orderBy('created_at', 'desc');

        // 3. Terapkan Filter Periode (Server Side)
        // Kita biarkan ini tetap ada untuk membatasi beban data jika periode dipilih
        if ($selectedPeriodeId) {
            $query->where('periode_id', $selectedPeriodeId);
        }
        
        /**
         * PERBAIKAN BUG:
         * Kita gunakan get() bukan paginate() agar JQuery DataTables di View 
         * bisa melihat semua data (termasuk status Menunggu Sidang).
         */
        $arsipTugasAkhir = $query->get(); 

        return view('staff.arsip-index', [
            'arsipTugasAkhir' => $arsipTugasAkhir,
            'periodes' => $periodes,
            'selectedPeriodeId' => $selectedPeriodeId,
        ]);
    }

    public function show(TugasAkhir $tugasAkhir)
    {
        $tugasAkhir->load([
            'mahasiswa', 
            'dosenPembimbing1', 
            'dosenPembimbing2', 
            'sidangs.beritaAcara', 
            'pengajuanSidangs.dokumen', 
            'periode'
        ]);

        return view('staff.arsip-detail', ['ta' => $tugasAkhir]);
    }
}