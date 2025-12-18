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

    public function getOrGenerateRevisi(Sidang $sidang)
    {
        return $this->processDocument(
            $sidang, 
            'LEMBAR_REVISI', 
            'mahasiswa.revisi_pdf', 
            'Revisi_Sidang_'
        );
    }

    public function getOrGenerateBeritaAcara(Sidang $sidang)
    {
        $sidang->load(['tugasAkhir.dosenPembimbing1', 'tugasAkhir.dosenPembimbing2']);
        return $this->processDocument(
            $sidang, 
            'BERITA_ACARA', 
            'mahasiswa.berita-acara-pdf', 
            'BA_Sidang_',
            ['ba' => $sidang->beritaAcara, 'mahasiswa' => $sidang->tugasAkhir->mahasiswa, 'ta' => $sidang->tugasAkhir]
        );
    }

    private function processDocument(Sidang $sidang, $jenisDokumen, $viewName, $prefixName, $extraData = [])
    {
        // 1. CARI FILE LAMA
        $existingDoc = DokumenHasilSidang::where('sidang_id', $sidang->id)
                                         ->where('jenis_dokumen', $jenisDokumen)
                                         ->latest()
                                         ->first();

        // 2. HAPUS FILE LAMA (PENTING AGAR TIDAK MUNCUL DATA LAMA TERUS)
        if ($existingDoc) {
            if (Storage::exists($existingDoc->path_file)) {
                Storage::delete($existingDoc->path_file);
            }
            $existingDoc->delete();
        }

        // 3. GENERATE PDF BARU (DENGAN VIEW YANG SUDAH DIBERSIHKAN)
        $data = array_merge(['sidang' => $sidang], $extraData);
        $pdf = Pdf::loadView($viewName, $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();

        // 4. HASHING & SIGNING
        $hashData = $this->signer->calculateHash($pdfContent);
        $signature = $this->signer->signWithSystemKey($hashData['raw_combined']);

        // 5. SIMPAN FILE BARU
        $nrp = $sidang->tugasAkhir->mahasiswa->nrp;
        $folder = strtolower($jenisDokumen);
        $filename = $prefixName . $nrp . '_' . time() . '.pdf';
        $path = "generated_docs/{$folder}/" . $filename;
        
        Storage::put($path, $pdfContent);

        // 6. SIMPAN RECORD BARU KE DB
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