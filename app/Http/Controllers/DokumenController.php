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
     * [EXISTING] Download Dokumen Pengajuan (Buku Skripsi, KHS, Transkrip)
     */
    public function download(Request $request, DokumenPengajuan $dokumen)
    {
        // 1. --- LOGIKA KEAMANAN ---
        $user = Auth::user();
        // Pastikan relasi 'pengajuanSidang' dan 'tugasAkhir' valid
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

        // 2. --- CEK KEBERADAAN FILE ---
        $path = $dokumen->path_penyimpanan;

        if (!Storage::exists($path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        // Ambil Absolute Path (Path lengkap di server)
        $absolutePath = Storage::path($path);

        // 3. --- VIEW vs DOWNLOAD ---
        if ($request->query('mode') === 'view') {
            return response()->file($absolutePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $dokumen->nama_file_asli . '"'
            ]);
        }

        // FIX: Gunakan response()->download() alih-alih Storage::download()
        return response()->download($absolutePath, $dokumen->nama_file_asli);
    }

    /**
     * [UPDATED] Download Hasil Sidang (Lembar Revisi & Berita Acara)
     */
    public function downloadHasilSidang(Request $request, $sidangId, $jenis)
    {
        // 1. Ambil Data Sidang
        $sidang = Sidang::with([
            'tugasAkhir.mahasiswa',
            'beritaAcara',
            'eventSidang',
            'lembarPenilaians.dosen',
            'dosenPengujiKetua',
            'dosenPengujiSekretaris',
            'tugasAkhir.dosenPembimbing1',
            'tugasAkhir.dosenPembimbing2'
        ])->findOrFail($sidangId);

        $user = Auth::user();

        // 2. Cek Hak Akses (Authorization)
        $isAuthorized = false;

        // a. Staff
        if (method_exists($user, 'hasRole') ? $user->hasRole('staff') : $user->staff_id) {
            $isAuthorized = true;
        }
        // b. Mahasiswa (Pemilik TA)
        elseif ($user->mahasiswa_id) {
            if ($sidang->tugasAkhir->mahasiswa_id === $user->mahasiswa_id) {
                $isAuthorized = true;
            }
        }
        // c. Dosen (Penguji/Pembimbing)
        elseif ($user->dosen_id) {
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
            $dokumen = $this->docHelper->generateRevisi($sidang);
        } elseif ($jenis === 'berita-acara') {
            if (!$sidang->beritaAcara) {
                // Opsional: Jika belum ada, coba generate on-the-fly
                $this->docHelper->getOrGenerateBeritaAcara($sidang);
                $sidang->refresh();
            }
            // Cek lagi setelah refresh
            if (!$sidang->beritaAcara) {
                 return back()->with('error', 'Berita Acara belum diterbitkan.');
            }
            $dokumen = $this->docHelper->getOrGenerateBeritaAcara($sidang);
        } else {
            abort(404, 'Jenis dokumen tidak dikenal.');
        }

        // 4. Pastikan File Fisik Ada (Gunakan Disk Public!)
        if (!Storage::disk('public')->exists($dokumen->path_file)) {
            // Coba generate ulang paksa (Anti-404 mechanism)
            if ($jenis === 'revisi') {
                $this->docHelper->generateRevisi($sidang);
            } elseif ($jenis === 'berita-acara') {
                $this->docHelper->getOrGenerateBeritaAcara($sidang);
            }
            
            // Cek lagi
            if (!Storage::disk('public')->exists($dokumen->path_file)) {
                 abort(404, 'File fisik dokumen tidak ditemukan di server.');
            }
        }

        // --- AMBIL FULL PATH DARI DISK PUBLIC ---
        $absolutePath = Storage::disk('public')->path($dokumen->path_file);

        // 5. Response View atau Download
        if ($request->query('mode') === 'view') {
            return response()->file($absolutePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $dokumen->nama_file_asli . '"'
            ]);
        }

        // FIX: Gunakan response()->download() dengan Absolute Path
        // Ini menghindari error "Call to unknown method ::download()"
        return response()->download($absolutePath, $dokumen->nama_file_asli);
    }
}