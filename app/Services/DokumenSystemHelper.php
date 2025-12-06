<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sidang;
use App\Models\DokumenHasilSidang;
use App\Services\SystemSignatureService;

class DokumenSystemHelper
{
    protected $signer;

    public function __construct(SystemSignatureService $signer)
    {
        $this->signer = $signer;
    }

    /**
     * Mengambil atau Membuat File Revisi
     */
    public function getOrGenerateRevisi(Sidang $sidang)
    {
        return $this->processDocument(
            $sidang, 
            'LEMBAR_REVISI', 
            'mahasiswa.sidang.revisi_pdf', 
            'Revisi_Sidang_'
        );
    }

    /**
     * Mengambil atau Membuat File Berita Acara
     */
    public function getOrGenerateBeritaAcara(Sidang $sidang)
    {
        // Pastikan relasi dosbing diload untuk BA
        $sidang->load(['tugasAkhir.dosenPembimbing1', 'tugasAkhir.dosenPembimbing2']);

        return $this->processDocument(
            $sidang, 
            'BERITA_ACARA', 
            'mahasiswa.berita-acara-pdf', 
            'BA_Sidang_',
            ['ba' => $sidang->beritaAcara, 'mahasiswa' => $sidang->tugasAkhir->mahasiswa, 'ta' => $sidang->tugasAkhir]
        );
    }

    /**
     * Logika Inti: Cek DB -> Jika null -> Generate -> Sign -> Save -> Return
     */
    private function processDocument(Sidang $sidang, $jenisDokumen, $viewName, $prefixName, $extraData = [])
    {
        // 1. CEK DATABASE
        $existingDoc = DokumenHasilSidang::where('sidang_id', $sidang->id)
                                         ->where('jenis_dokumen', $jenisDokumen)
                                         ->latest()
                                         ->first();

        // Jika ada di DB dan Fisik, kembalikan datanya
        if ($existingDoc && Storage::exists($existingDoc->path_file)) {
            return $existingDoc;
        }

        // 2. GENERATE PDF
        $data = array_merge(['sidang' => $sidang], $extraData);
        $pdf = Pdf::loadView($viewName, $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();

        // 3. HASHING & SIGNING
        $hashData = $this->signer->calculateHash($pdfContent);
        $signature = $this->signer->signWithSystemKey($hashData['raw_combined']);

        // 4. SIMPAN FILE
        $nrp = $sidang->tugasAkhir->mahasiswa->nrp;
        $folder = strtolower($jenisDokumen); // lembar_revisi atau berita_acara
        $filename = $prefixName . $nrp . '_' . time() . '.pdf';
        $path = "generated_docs/{$folder}/" . $filename;
        
        Storage::put($path, $pdfContent);

        // 5. SIMPAN KE DB
        return DokumenHasilSidang::create([
            'sidang_id' => $sidang->id,
            'jenis_dokumen' => $jenisDokumen,
            'path_file' => $path,
            'nama_file_asli' => $filename,
            'hash_sha512_full' => $hashData['sha512'],
            'hash_blake2b_full' => $hashData['blake2b'],
            'hash_combined' => $hashData['combined'],
            'signature_data' => $signature,
        ]);
    }
}