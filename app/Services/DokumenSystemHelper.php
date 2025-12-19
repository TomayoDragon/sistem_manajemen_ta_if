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
     * [REVISI V2] Generate Form Revisi TERPISAH per Dosen
     * Mendukung kasus jika hanya ada 1 Pembimbing & menyimpan ID Dosen.
     */
    public function getOrGenerateRevisi(Sidang $sidang)
    {
        // 1. Kumpulkan semua Dosen yang terlibat
        // Struktur: ['Role' => Object Dosen]
        $daftarDosen = [
            'PEMBIMBING_1' => $sidang->tugasAkhir->dosenPembimbing1,
            'PEMBIMBING_2' => $sidang->tugasAkhir->dosenPembimbing2, // Bisa NULL
            'KETUA_PENGUJI' => $sidang->dosenPengujiKetua,
            'SEKRETARIS'   => $sidang->dosenPengujiSekretaris,
        ];

        // 2. FILTER NULL (Kunci agar tidak error saat Dosbing 2 kosong)
        // Array_filter otomatis membuang data yang kosong/null
        $daftarDosenValid = array_filter($daftarDosen);

        $generatedDocuments = [];

        // 3. LOOPING: Generate Dokumen untuk setiap Dosen yang valid
        foreach ($daftarDosenValid as $role => $dosen) {
            
            // Tentukan Jenis Dokumen Unik (Misal: LEMBAR_REVISI_PEMBIMBING_1)
            $jenisDokumenUnik = 'LEMBAR_REVISI_' . $role;
            
            // Nama file prefix (Misal: Revisi_Budi_PakJoko_)
            $prefixName = 'Revisi_' . str_replace(' ', '_', $dosen->nama_lengkap) . '_';

            // Panggil fungsi core dengan mengirimkan ID DOSEN
            $doc = $this->processDocument(
                $sidang,
                $jenisDokumenUnik,      // Jenis Dokumen Spesifik
                'mahasiswa.revisi_pdf', // View Blade
                $prefixName,
                [
                    'dosen' => $dosen,  // Kirim data dosen spesifik ke View
                    'peran' => str_replace('_', ' ', $role) // Kirim nama peran ke View
                ],
                $dosen->id // <--- PENTING: Simpan ID Dosen ke DB
            );

            $generatedDocuments[] = $doc;
        }

        // Return ARRAY dokumen (karena sekarang jamak)
        return $generatedDocuments; 
    }

    /**
     * [REVISI] Generate Berita Acara (Tetap 1 File)
     * Tapi disesuaikan agar aman jika Pembimbing 2 Null.
     */
    public function getOrGenerateBeritaAcara(Sidang $sidang)
    {
        // Load relasi agar data lengkap
        $sidang->load(['tugasAkhir.dosenPembimbing1', 'tugasAkhir.dosenPembimbing2']);

        // Data yang dikirim ke View
        $dataView = [
            'ba' => $sidang->beritaAcara,
            'mahasiswa' => $sidang->tugasAkhir->mahasiswa,
            'ta' => $sidang->tugasAkhir,
            // Kirim dosen secara eksplisit agar View lebih mudah
            'dosbing1' => $sidang->tugasAkhir->dosenPembimbing1,
            'dosbing2' => $sidang->tugasAkhir->dosenPembimbing2, // Bisa Null (Handle di Blade @if)
            'penguji1' => $sidang->dosenPengujiKetua,
            'penguji2' => $sidang->dosenPengujiSekretaris
        ];

        return $this->processDocument(
            $sidang, 
            'BERITA_ACARA', 
            'mahasiswa.berita-acara-pdf', 
            'BA_Sidang_',
            $dataView,
            null // <--- Berita Acara milik umum (tidak ada ID Dosen spesifik)
        );
    }

    /**
     * Fungsi Inti Pemrosesan Dokumen
     * Menerima parameter $dosenId (nullable)
     */
    private function processDocument(Sidang $sidang, $jenisDokumen, $viewName, $prefixName, $extraData = [], $dosenId = null)
    {
        // 1. CARI FILE LAMA (Cek berdasarkan Jenis & Dosen ID)
        $query = DokumenHasilSidang::where('sidang_id', $sidang->id)
                                   ->where('jenis_dokumen', $jenisDokumen);
        
        if ($dosenId) {
            $query->where('dosen_id', $dosenId);
        }

        $existingDoc = $query->latest()->first();

        // 2. HAPUS FILE LAMA (Reset ulang jika digenerate)
        if ($existingDoc) {
            if (Storage::exists($existingDoc->path_file)) {
                Storage::delete($existingDoc->path_file);
            }
            $existingDoc->delete();
        }

        // 3. GENERATE PDF BARU
        // Gabungkan data standar sidang dengan data ekstra (dosen spesifik, dll)
        $data = array_merge(['sidang' => $sidang], $extraData);
        
        $pdf = Pdf::loadView($viewName, $data)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();

        // 4. HASHING & SIGNING (Digital Signature)
        $hashData = $this->signer->calculateHash($pdfContent);
        $signature = $this->signer->signWithSystemKey($hashData['raw_combined']);

        // 5. SIMPAN FILE KE STORAGE
        $nrp = $sidang->tugasAkhir->mahasiswa->nrp;
        // Folder berdasarkan prefix jenis (misal: lembar_revisi) agar rapi
        // Explode untuk ambil kata depan (LEMBAR_REVISI -> lembar_revisi)
        $folderParts = explode('_', $jenisDokumen);
        $folderName = strtolower($folderParts[0] . '_' . ($folderParts[1] ?? 'doc'));
        
        $filename = $prefixName . $nrp . '_' . time() . '.pdf';
        $path = "generated_docs/{$folderName}/" . $filename;
        
        Storage::put($path, $pdfContent);

        // 6. SIMPAN RECORD KE DATABASE (Termasuk Dosen ID)
        return DokumenHasilSidang::create([
            'sidang_id' => $sidang->id,
            'dosen_id' => $dosenId, // <--- Simpan ID Dosen pemilik dokumen (atau NULL)
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