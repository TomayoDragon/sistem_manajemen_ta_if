<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Models
use App\Models\DokumenPengajuan;
use App\Models\DokumenHasilSidang;

// Services
use App\Services\SignatureService;
use App\Services\SystemSignatureService; 

class IntegritasController extends Controller
{
    private function findDocument($id, $source)
    {
        if ($source === 'system') {
            return DokumenHasilSidang::findOrFail($id);
        }
        return DokumenPengajuan::findOrFail($id);
    }

    public function show(Request $request, $id)
    {
        $source = $request->query('source', 'upload');
        $dokumen = $this->findDocument($id, $source);

        if ($source === 'system') {
            $dokumen->load('sidang.tugasAkhir.mahasiswa');
        } else {
            $dokumen->load('pengajuanSidang.tugasAkhir.mahasiswa');
        }

        $layout = 'app-layout';
        if (Auth::user()->hasRole('mahasiswa')) $layout = 'mahasiswa-layout';
        if (Auth::user()->hasRole('dosen')) $layout = 'dosen-layout';

        return view('integritas-check', [
            'dokumen' => $dokumen,
            'source'  => $source,
            'layout'  => $layout
        ]);
    }

    public function verify(Request $request, $id, SignatureService $signatureService, SystemSignatureService $systemService)
    {
        $source = $request->input('source', 'upload');
        $dokumen = $this->findDocument($id, $source);
        
        $request->validate(['file_cek' => 'required|file|max:51200']);

        // 1. AMBIL SIGNATURE DARI DB & KONVERSI JIKA PERLU
        $signatureDB = $dokumen->signature_data;
        $signatureToVerify = '';

        if ($source === 'system') {
            // Sistem menyimpan BASE64 (agar aman dari error UTF-8), jadi harus di-DECODE ke binary
            $signatureToVerify = base64_decode($signatureDB);
        } else {
            // Mahasiswa menyimpan BINARY, jadi pakai langsung
            $signatureToVerify = $signatureDB;
        }

        // 2. TENTUKAN PUBLIC KEY (BASE64)
        $publicKey = null;
        if ($source === 'system') {
            try {
                $publicKey = $systemService->getPublicKey();
            } catch (\Exception $e) {
                return back()->with('error', 'Kunci Sistem belum diset.');
            }
        } else {
            $mahasiswa = $dokumen->pengajuanSidang->tugasAkhir->mahasiswa;
            $publicKey = $mahasiswa->public_key;
        }

        // 3. HASH FILE UPLOAD (Menggunakan Logic Mahasiswa/Standard)
        $fileContent = $request->file('file_cek')->get();
        $hashData = $signatureService->performCustomHash($fileContent);
        
        // Pastikan key ini sesuai dengan output SignatureService::performCustomHash Anda
        $fileHashBin = $hashData['combined_raw_for_signing']; 

        // 4. VERIFIKASI (Sodium butuh: Signature Binary, Hash Binary, Key Base64)
        $isValid = $signatureService->verifySignature(
            $signatureToVerify, 
            $fileHashBin,     
            $publicKey       
        );

        return redirect()->route('integritas.show', ['dokumen' => $id, 'source' => $source])
            ->with([
                'checkResult' => $isValid,
            ]);
    }
}