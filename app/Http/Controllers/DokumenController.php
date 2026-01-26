<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

// Models
use App\Models\DokumenPengajuan;
use App\Models\Sidang;

// Services
use App\Services\DokumenSystemHelper;

class DokumenController extends Controller
{
    protected $docHelper;

    public function __construct(DokumenSystemHelper $docHelper)
    {
        $this->docHelper = $docHelper;
    }

    /**
     * Download Dokumen Pengajuan (Buku Skripsi, KHS, Transkrip)
     */
    public function download(Request $request, DokumenPengajuan $dokumen)
    {
        // 1. --- LOGIKA KEAMANAN ---
        $user = Auth::user();
        $taOwnerId = $dokumen->pengajuanSidang->tugasAkhir->mahasiswa_id;

        $isAuthorized = false;
        if ($user->hasRole('staff') || $user->hasRole('dosen')) {
            $isAuthorized = true;
        } elseif ($user->hasRole('mahasiswa') && $user->mahasiswa_id === $taOwnerId) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'ANDA TIDAK BERHAK MENGAKSES FILE INI.');
        }

        $path = $dokumen->path_penyimpanan;

        if (!Storage::exists($path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan.');
        }

        $absolutePath = Storage::path($path);

        // --- PERBAIKAN: Deteksi Mime Type secara Dinamis ---
        $mimeType = Storage::mimeType($path);

        // 3. --- VIEW vs DOWNLOAD ---
        if ($request->query('mode') === 'view') {
            // Untuk mp4 atau zip, mode 'view' biasanya akan membuka player atau file explorer browser
            return response()->file($absolutePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $dokumen->nama_file_asli . '"'
            ]);
        }

        // Untuk download langsung
        return response()->download($absolutePath, $dokumen->nama_file_asli, [
            'Content-Type' => $mimeType
        ]);

    }

    /**
     * Download Hasil Sidang (Lembar Revisi & Berita Acara)
     */
    public function downloadHasilSidang(Request $request, $sidangId, $jenis)
    {
        // 1. Ambil Data Sidang dengan Eager Loading lengkap
        $sidang = Sidang::with([
            'tugasAkhir.mahasiswa',
            'beritaAcara',
            'eventSidang',
            'lembarPenilaians.dosen',
            'lembarPenilaians.detailRevisis', // Penting untuk memuat komentar mahasiswa
            'dosenPengujiKetua',
            'dosenPengujiSekretaris',
            'tugasAkhir.dosenPembimbing1',
            'tugasAkhir.dosenPembimbing2'
        ])->findOrFail($sidangId);

        $user = Auth::user();

        // 2. Cek Hak Akses (Authorization)
        $isAuthorized = false;
        if (method_exists($user, 'hasRole') ? $user->hasRole('staff') : $user->staff_id) {
            $isAuthorized = true;
        } elseif ($user->mahasiswa_id) {
            if ($sidang->tugasAkhir->mahasiswa_id === $user->mahasiswa_id) {
                $isAuthorized = true;
            }
        } elseif ($user->dosen_id) {
            $isPenguji = $sidang->dosen_penguji_ketua_id == $user->dosen_id ||
                $sidang->dosen_penguji_sekretaris_id == $user->dosen_id;
            $isPembimbing = $sidang->tugasAkhir->dosen_pembimbing_1_id == $user->dosen_id ||
                $sidang->tugasAkhir->dosen_pembimbing_2_id == $user->dosen_id;
            if ($isPenguji || $isPembimbing) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // 3. Generate atau Ambil Dokumen via Helper
        $dokumen = null;
        if ($jenis === 'revisi') {
            /** * PAKSA GENERATE ULANG (true) 
             * Agar setiap kali download, file PDF dibuat baru menggunakan 
             * data komentar terbaru dari database.
             */
            $dokumen = $this->docHelper->generateRevisi($sidang, true);
        } elseif ($jenis === 'berita-acara') {
            if (!$sidang->beritaAcara) {
                $this->docHelper->getOrGenerateBeritaAcara($sidang);
                $sidang->refresh();
            }
            if (!$sidang->beritaAcara) {
                return back()->with('error', 'Berita Acara belum diterbitkan.');
            }
            $dokumen = $this->docHelper->getOrGenerateBeritaAcara($sidang);
        } else {
            abort(404, 'Jenis dokumen tidak dikenal.');
        }

        // 4. Pastikan File Fisik Ada
        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            // Jika file tidak ada, coba generate ulang secara paksa
            if ($jenis === 'revisi') {
                $dokumen = $this->docHelper->generateRevisi($sidang, true);
            } else {
                $dokumen = $this->docHelper->getOrGenerateBeritaAcara($sidang);
            }

            if (!Storage::disk('public')->exists($dokumen->path_file)) {
                abort(404, 'File fisik dokumen tidak ditemukan di server.');
            }
        }

        // 5. Response View atau Download
        $absolutePath = Storage::disk('public')->path($dokumen->path_file);

        if ($request->query('mode') === 'view') {
            return response()->file($absolutePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $dokumen->nama_file_asli . '"'
            ]);
        }

        return response()->download($absolutePath, $dokumen->nama_file_asli);
    }
}