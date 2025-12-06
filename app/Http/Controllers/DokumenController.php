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
     * Logic ini dari kode yang kamu kirimkan.
     */
    public function download(Request $request, DokumenPengajuan $dokumen)
    {
        // 1. --- LOGIKA KEAMANAN (SAMA SEPERTI SEBELUMNYA) ---
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

        // 2. --- CEK KEBERADAAN FILE ---
        $path = $dokumen->path_penyimpanan;

        if (!Storage::exists($path)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        // 3. --- LOGIKA BARU: VIEW vs DOWNLOAD ---

        // Jika ada parameter ?mode=view di URL, tampilkan di browser (inline)
        if ($request->query('mode') === 'view') {
            // response()->file() mengirim header 'Content-Disposition: inline'
            // Ini memaksa browser membuka file (jika PDF/Gambar) alih-alih download
            return response()->file(storage_path('app/private/' . $path));
        }

        // Default: Download paksa (Content-Disposition: attachment)
        return Storage::download($path, $dokumen->nama_file_asli);
    }

    /**
     * [BARU] Download Hasil Sidang (Lembar Revisi & Berita Acara)
     * Menggunakan DokumenSystemHelper untuk auto-generate & sign.
     */
    public function downloadHasilSidang($sidangId, $jenis)
    {
        $sidang = Sidang::with([
            'tugasAkhir.mahasiswa',
            'beritaAcara',
            'eventSidang',
            'lembarPenilaians.dosen',
            'dosenPengujiKetua',        // Load data penguji
            'dosenPengujiSekretaris',   // Load data penguji
            'tugasAkhir.dosenPembimbing1', // Load dosbing
            'tugasAkhir.dosenPembimbing2'  // Load dosbing
        ])->findOrFail($sidangId);

        $user = Auth::user();

        // --- AUTHORIZATION CHECK ---
        $isAuthorized = false;

        // 1. Staff: Boleh semua
        if (method_exists($user, 'hasRole') ? $user->hasRole('staff') : $user->staff_id) {
            $isAuthorized = true;
        }
        // 2. Mahasiswa: Hanya miliknya sendiri
        elseif ($user->mahasiswa_id) {
            if ($sidang->tugasAkhir->mahasiswa_id === $user->mahasiswa_id) {
                $isAuthorized = true;
            }
        }
        // 3. Dosen: Hanya jika dia terlibat (Pembimbing / Penguji)
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
            abort(403, 'Anda tidak memiliki akses ke hasil sidang ini.');
        }

        // --- PROSES DOWNLOAD MENGGUNAKAN HELPER ---
        $dokumen = null;

        if ($jenis === 'revisi') {
            $dokumen = $this->docHelper->getOrGenerateRevisi($sidang);
        } elseif ($jenis === 'berita-acara') {
            // Cek apakah sidang sudah dinilai dan BA terbit
            if (!$sidang->beritaAcara) {
                return back()->with('error', 'Berita Acara belum diterbitkan.');
            }
            $dokumen = $this->docHelper->getOrGenerateBeritaAcara($sidang);
        } else {
            abort(404);
        }

        return Storage::download($dokumen->path_file, $dokumen->nama_file_asli);
    }
}