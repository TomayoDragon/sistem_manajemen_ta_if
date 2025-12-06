<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

// Models
use App\Models\Sidang;
use App\Models\DokumenHasilSidang;

// Services
use App\Services\SystemSignatureService;

class BeritaAcaraController extends Controller
{
    /**
     * Download Berita Acara dengan Digital Signature Sistem
     */
    public function show($sidangId, SystemSignatureService $signer)
    {
        // 1. Ambil Data Sidang beserta Berita Acaranya
        $sidang = Sidang::with([
            'tugasAkhir.mahasiswa',
            'beritaAcara', 
            'eventSidang',
            'dosenPengujiKetua',
            'dosenPengujiSekretaris',
            'tugasAkhir.dosenPembimbing1', // Load Dosbing 1
            'tugasAkhir.dosenPembimbing2'  // Load Dosbing 2
        ])->findOrFail($sidangId);

        // 2. SECURITY CHECK
        $mahasiswaLogin = Auth::user()->mahasiswa;
        if ($sidang->tugasAkhir->mahasiswa_id !== $mahasiswaLogin->id) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        // Cek ketersediaan BA
        if (!$sidang->beritaAcara) {
            return back()->with('error', 'Berita Acara belum diterbitkan oleh Dosen Penguji.');
        }

        // 3. CEK DATABASE (File Lama)
        $existingDoc = DokumenHasilSidang::where('sidang_id', $sidang->id)
                                         ->where('jenis_dokumen', 'BERITA_ACARA')
                                         ->latest()
                                         ->first();

        if ($existingDoc && Storage::exists($existingDoc->path_file)) {
            return Storage::download($existingDoc->path_file, $existingDoc->nama_file_asli);
        }

        // ==========================================================
        // PROSES GENERATE BARU
        // ==========================================================

        // 4. GENERATE PDF (IN MEMORY)
        // PERBAIKAN: Menambahkan 'mahasiswa' dan 'ta' ke view
        // Sesuaikan nama view dengan lokasi file kamu: resources/views/mahasiswa/berita-acara-pdf.blade.php
        $pdf = Pdf::loadView('mahasiswa.berita-acara-pdf', [
            'sidang'    => $sidang,
            'ba'        => $sidang->beritaAcara,
            'mahasiswa' => $sidang->tugasAkhir->mahasiswa, // <-- DITAMBAHKAN
            'ta'        => $sidang->tugasAkhir             // <-- DITAMBAHKAN
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();

        // 5. HASHING
        $hashData = $signer->calculateHash($pdfContent);

        // 6. SIGNING
        try {
            $signature = $signer->signWithSystemKey($hashData['raw_combined']);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menandatangani Berita Acara: ' . $e->getMessage());
        }

        // 7. SIMPAN FILE FISIK
        $filename = 'BA_Sidang_' . $mahasiswaLogin->nrp . '_' . time() . '.pdf';
        $path = 'generated_docs/berita_acara/' . $filename;
        
        Storage::put($path, $pdfContent);

        // 8. SIMPAN KE DATABASE
        DokumenHasilSidang::create([
            'sidang_id' => $sidang->id,
            'jenis_dokumen' => 'BERITA_ACARA',
            'path_file' => $path,
            'nama_file_asli' => $filename,
            'hash_sha512_full' => $hashData['sha512'],
            'hash_blake2b_full' => $hashData['blake2b'],
            'hash_combined' => $hashData['combined'],
            'signature_data' => $signature, 
        ]);

        // 9. DOWNLOAD
        return Storage::download($path, $filename);
    }
}