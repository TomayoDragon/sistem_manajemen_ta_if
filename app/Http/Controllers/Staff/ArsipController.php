<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TugasAkhir;
use App\Models\Periode; // <-- UBAH: Kita butuh Periode, bukan Event

class ArsipController extends Controller
{
    /**
     * Menampilkan halaman Arsip TA dengan fitur PENCARIAN dan FILTER PERIODE.
     */
    public function index(Request $request)
    {
        // 1. Ambil data dari request (search & filter)
        $searchQuery = $request->search;
        $selectedPeriodeId = $request->periode_id; // <-- UBAH: dari event_sidang_id

        // 2. Ambil SEMUA periode untuk mengisi dropdown
        $periodes = Periode::orderBy('tahun_akademik', 'desc')->orderBy('semester', 'desc')->get();

        // 3. Query utama untuk mengambil data TA
        $query = TugasAkhir::query()
                            ->with('mahasiswa', 'dosenPembimbing1', 'dosenPembimbing2', 'periode') // <-- Tambah 'periode'
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
            // Ini akan mem-filter langsung di 'tugas_akhirs.periode_id'
            // Ini akan mencakup mahasiswa 'Bimbingan' di periode tsb
            $query->where('periode_id', $selectedPeriodeId);
        }
        
        // 6. Ambil data dengan pagination
        $arsipTugasAkhir = $query->paginate(20)->withQueryString();

        // 7. Kirim semua data ke view
        return view('staff.arsip-index', [
            'arsipTugasAkhir' => $arsipTugasAkhir,
            'periodes' => $periodes, // <-- Data untuk dropdown
            'searchQuery' => $searchQuery,
            'selectedPeriodeId' => $selectedPeriodeId, // <-- Untuk menandai yg terpilih
        ]);
    }

    /**
     * Menampilkan detail TA (LOGIKA DIPERBARUI)
     */
    public function show(TugasAkhir $tugasAkhir)
    {
        // Muat SEMUA relasi yang kita butuhkan untuk halaman detail
        $tugasAkhir->load(
            'mahasiswa', 
            'dosenPembimbing1', 
            'dosenPembimbing2', 
            'lstas',  // Semua jadwal LSTA
            'sidangs', // Semua jadwal Sidang
            'pengajuanSidangs.dokumen', // Semua paket pengajuan, DAN dokumen di dalamnya
            'pengajuanSidangs.validator', // Siapa staf yg memvalidasi
            'periode' // <-- Muat juga data periode
        );

        return view('staff.arsip-detail', [
            'ta' => $tugasAkhir
        ]);
    }
}