<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sidang;
use Illuminate\Support\Facades\Storage;
use App\Services\DokumenSystemHelper;

class SidangController extends Controller
{
    /**
     * Menampilkan halaman jadwal Sidang / LSTA.
     */
    public function index()
    {
        // 1. Ambil TA aktif mahasiswa
        $tugasAkhir = Auth::user()->mahasiswa
            ->tugasAkhirs()
            ->latest()
            ->first();

        // 2. Jika tidak punya TA, kembalikan
        if (!$tugasAkhir) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda belum memiliki data Tugas Akhir.');
        }

        // 3. Ambil pengajuan terakhir untuk cek status
        $pengajuanTerbaru = $tugasAkhir->pengajuanSidangs()
            ->latest()
            ->first();

        // 4. Inisialisasi jadwal
        $lstaTerbaru = null;
        $sidangTerbaru = null;

        // 5. HANYA JIKA status = TERIMA, cari jadwalnya
        if ($pengajuanTerbaru && $pengajuanTerbaru->status_validasi == 'TERIMA') {

            $lstaTerbaru = $tugasAkhir->lstas()->latest()->first();

            // Ambil jadwal Sidang terbaru DAN data Berita Acaranya (jika ada)
            $sidangTerbaru = $tugasAkhir->sidangs()
                ->with('beritaAcara') // Eager-load relasi Berita Acara
                ->latest()
                ->first();
        }

        // 6. Kirim semua data ke view
        return view('mahasiswa.sidang', [
            'pengajuanTerbaru' => $pengajuanTerbaru,
            'lsta' => $lstaTerbaru,
            'sidang' => $sidangTerbaru,
        ]);
    }

    /**
     * Download File Revisi Gabungan
     */
    public function downloadRevisi($id)
    {
        // 1. Ambil Data Sidang
        $sidang = Sidang::with('tugasAkhir.mahasiswa')->findOrFail($id);

        // 2. Security Check (Pastikan pemilik sidang)
        $mahasiswaLogin = Auth::user()->mahasiswa;
        if ($sidang->tugasAkhir->mahasiswa_id !== $mahasiswaLogin->id) {
            abort(403, 'Akses Ditolak.');
        }

        // 3. Tentukan Path File
        $nrp = $sidang->tugasAkhir->mahasiswa->nrp;
        $fileName = "Revisi_Gabungan_{$nrp}_{$sidang->id}.pdf";

        // Path relatif di storage/app/public/
        $relativePath = "uploads/revisi/{$fileName}";

        // 4. Cek File & Generate jika belum ada
        if (!Storage::disk('public')->exists($relativePath)) {
            try {
                // === PERBAIKAN DI SINI (FIX STATIC CALL) ===

                // A. Load relasi dulu agar Helper tidak error "null property"
                $sidang->load(['dosenPengujiKetua', 'dosenPengujiSekretaris', 'tugasAkhir.dosenPembimbing1', 'tugasAkhir.dosenPembimbing2']);

                // B. Panggil Helper menggunakan 'app()' karena methodnya Non-Static
                $helper = app(DokumenSystemHelper::class);
                $helper->generateRevisi($sidang);

                // Cek lagi apakah berhasil digenerate
                if (!Storage::disk('public')->exists($relativePath)) {
                    return back()->with('error', 'Dokumen gagal digenerate.');
                }

            } catch (\Exception $e) {
                return back()->with('error', 'Gagal men-generate dokumen revisi: ' . $e->getMessage());
            }
        }

        // 5. Download File
        $fullPath = Storage::disk('public')->path($relativePath);

        return response()->file($fullPath);
    }
}