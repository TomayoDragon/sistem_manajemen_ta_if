<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DokumenPengajuan;
use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IntegritasController extends Controller
{
    /**
     * Fungsi Helper Private untuk Mengecek Hak Akses.
     * Jika user tidak berhak, kode ini akan langsung membatalkan proses (Abort 403).
     */
    private function authorizeAccess(DokumenPengajuan $dokumen)
    {
        $user = Auth::user();
        
        // Ambil data Tugas Akhir terkait dokumen ini
        $tugasAkhir = $dokumen->pengajuanSidang->tugasAkhir;

        // 1. Cek Role MAHASISWA
        if ($user->hasRole('mahasiswa')) {
            // Mahasiswa hanya boleh akses jika ini dokumen miliknya sendiri
            if ($tugasAkhir->mahasiswa_id !== $user->mahasiswa_id) {
                abort(403, 'AKSES DITOLAK. Anda tidak berhak mengecek integritas dokumen mahasiswa lain.');
            }
        }
        
        // 2. Cek Role DOSEN
        elseif ($user->hasRole('dosen')) {
            $dosenId = $user->dosen_id;

            // Cek apakah dia Pembimbing 1 atau 2
            $isPembimbing = ($tugasAkhir->dosen_pembimbing_1_id == $dosenId || $tugasAkhir->dosen_pembimbing_2_id == $dosenId);

            // Cek apakah dia Penguji (Ketua/Sekretaris) di Sidang manapun untuk TA ini
            $isPengujiSidang = $tugasAkhir->sidangs()->where(function($q) use ($dosenId) {
                $q->where('dosen_penguji_ketua_id', $dosenId)
                  ->orWhere('dosen_penguji_sekretaris_id', $dosenId);
            })->exists();

            // Cek apakah dia Penguji di LSTA manapun untuk TA ini
            $isPengujiLsta = $tugasAkhir->lstas()->where('dosen_penguji_id', $dosenId)->exists();

            if (!$isPembimbing && !$isPengujiSidang && !$isPengujiLsta) {
                abort(403, 'AKSES DITOLAK. Anda bukan Pembimbing atau Penguji mahasiswa ini.');
            }
        }
        
        // 3. Role STAFF
        elseif ($user->hasRole('staff')) {
            // Staff PAJ diperbolehkan mengakses semua dokumen untuk keperluan validasi
            return true;
        }

        // Jika role tidak dikenali (misal Admin), kita tolak atau izinkan sesuai kebutuhan.
        // Untuk keamanan maksimal, defaultnya kita tolak jika bukan Staff/DosenTerkait/Pemilik.
        else {
             // Jika Anda ingin Admin bisa akses, ubah logika ini. 
             // Saat ini Admin akan kena block kecuali ditambahkan kondisinya.
             if (!$user->hasRole('admin')) {
                 abort(403, 'AKSES DITOLAK.');
             }
        }
    }

    /**
     * Menampilkan halaman verifikasi (Per-File).
     */
    public function show(DokumenPengajuan $dokumen)
    {
        // --- STEP 1: CEK OTORISASI ---
        $this->authorizeAccess($dokumen);
        // -----------------------------

        $dokumen->load('pengajuanSidang.tugasAkhir.mahasiswa');
        $layout = $this->getLayoutForUser(Auth::user());
        
        return view('integritas-check', [
            'dokumen' => $dokumen,
            'layout' => $layout
        ]);
    }

    /**
     * Memproses 1 file yang diupload untuk dicek.
     */
    public function verify(Request $request, DokumenPengajuan $dokumen, SignatureService $signatureService)
    {
        // --- STEP 1: CEK OTORISASI ---
        $this->authorizeAccess($dokumen);
        // -----------------------------

        $request->validate([
            'file_cek' => 'required|file|max:20480',
        ]);

        $originalHash = $dokumen->hash_combined;
        $fileContent = $request->file('file_cek')->get();
        
        // Hitung hash baru
        $hashData = $signatureService->performCustomHash($fileContent);
        $newHash = $hashData['combined_hex'];

        // Bandingkan
        $isMatch = ($originalHash === $newHash);
        
        return redirect()->route('integritas.show', $dokumen->id)
            ->with([
                'checkResult' => $isMatch,
                'newHash' => $newHash,
            ]);
    }
    
    private function getLayoutForUser($user)
    {
        if ($user->hasRole('mahasiswa')) return 'mahasiswa-layout';
        if ($user->hasRole('dosen')) return 'dosen-layout';
        if ($user->hasRole('staff')) return 'staff-layout';
        return 'app-layout'; 
    }
}