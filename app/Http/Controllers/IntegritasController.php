<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DokumenPengajuan;
use App\Services\SignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IntegritasController extends Controller
{
    private function authorizeAccess(DokumenPengajuan $dokumen)
    {
        $user = Auth::user();
        $tugasAkhir = $dokumen->pengajuanSidang->tugasAkhir;

        if ($user->hasRole('mahasiswa')) {
            if ($tugasAkhir->mahasiswa_id !== $user->mahasiswa_id) {
                abort(403, 'AKSES DITOLAK.');
            }
        } elseif ($user->hasRole('dosen')) {
            $dosenId = $user->dosen_id;
            $isPembimbing = ($tugasAkhir->dosen_pembimbing_1_id == $dosenId || $tugasAkhir->dosen_pembimbing_2_id == $dosenId);
            
            $isPengujiSidang = $tugasAkhir->sidangs()->where(function($q) use ($dosenId) {
                $q->where('dosen_penguji_ketua_id', $dosenId)->orWhere('dosen_penguji_sekretaris_id', $dosenId);
            })->exists();
            $isPengujiLsta = $tugasAkhir->lstas()->where('dosen_penguji_id', $dosenId)->exists();

            if (!$isPembimbing && !$isPengujiSidang && !$isPengujiLsta) {
                abort(403, 'AKSES DITOLAK. Anda bukan dosen terkait.');
            }
        } elseif ($user->hasRole('staff')) {
            return true;
        } else {
             if (!$user->hasRole('admin')) abort(403);
        }
    }

    public function show(DokumenPengajuan $dokumen)
    {
        $this->authorizeAccess($dokumen);
        $dokumen->load('pengajuanSidang.tugasAkhir.mahasiswa');
        
        $layout = 'app-layout';
        if (Auth::user()->hasRole('mahasiswa')) $layout = 'mahasiswa-layout';
        if (Auth::user()->hasRole('dosen')) $layout = 'dosen-layout';
        if (Auth::user()->hasRole('staff')) $layout = 'staff-layout';

        return view('integritas-check', [
            'dokumen' => $dokumen,
            'layout' => $layout
        ]);
    }

    /**
     * Memproses verifikasi menggunakan DIGITAL SIGNATURE (ASLI).
     */
    public function verify(Request $request, DokumenPengajuan $dokumen, SignatureService $signatureService)
    {
        $this->authorizeAccess($dokumen);

        $request->validate([
            'file_cek' => 'required|file|max:20480',
        ]);

        // 1. Ambil Data Kunci & Signature dari Database
        $storedSignature = $dokumen->signature_data; // Signature Biner Asli
        $mahasiswa = $dokumen->pengajuanSidang->tugasAkhir->mahasiswa;
        $publicKey = $mahasiswa->public_key; // Public Key Mahasiswa

        // 2. Proses File Baru (Hashing Saja)
        $fileContent = $request->file('file_cek')->get();
        $hashData = $signatureService->performCustomHash($fileContent);
        $newHashRaw = $hashData['combined_raw_for_signing']; // Hash biner dari file baru
        
        // 3. LAKUKAN VERIFIKASI SIGNATURE (MENGGUNAKAN PUBLIC KEY)
        // Ini memastikan Authenticity (Asli dari pemilik) & Non-Repudiation
        $isValid = $signatureService->verifySignature(
            $storedSignature, 
            $newHashRaw, 
            $publicKey
        );
        
        // 4. Kembalikan Hasil
        return redirect()->route('integritas.show', $dokumen->id)
            ->with([
                'checkResult' => $isValid, // True/False
                'newHash' => $hashData['combined_hex'], // Untuk ditampilkan jika gagal
            ]);
    }
}