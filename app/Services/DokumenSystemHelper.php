<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sidang;
use App\Models\DokumenHasilSidang;
use App\Services\SystemSignatureService;
use App\Models\Dosen;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DokumenSystemHelper
{
    protected $signer;

    public function __construct(SystemSignatureService $signer)
    {
        $this->signer = $signer;
    }

    /**
     * GENERATE REVISI (GABUNGAN)
     */
    public function generateRevisi(Sidang $sidang)
    {
        // 1. Siapkan Data View
        $data = [
            'mahasiswa' => $sidang->tugasAkhir->mahasiswa,
            'judul' => $sidang->tugasAkhir->judul,
            'tanggal_sidang' => $sidang->jadwal,
            'qr_code' => true,
            // Menggunakan fungsi getDaftarPenguji yang sudah diupdate
            'daftarPenguji' => $this->getDaftarPenguji($sidang) 
        ];

        // 2. Generate PDF
        $pdf = Pdf::loadView('mahasiswa.revisi_pdf', $data)->setPaper('a4', 'portrait');
        $content = $pdf->output();

        // 3. Simpan & DB
        return $this->saveDocument($sidang, 'LEMBAR_REVISI', 'Revisi_Gabungan_', 'revisi', $content);
    }

    /**
     * GENERATE BERITA ACARA
     */
    public function getOrGenerateBeritaAcara(Sidang $sidang)
    {
        // Load relasi view
        $sidang->load(['tugasAkhir.dosenPembimbing1', 'tugasAkhir.dosenPembimbing2', 'beritaAcara']);

        $data = [
            'sidang' => $sidang, 
            'ba' => $sidang->beritaAcara,
            'mahasiswa' => $sidang->tugasAkhir->mahasiswa,
            'ta' => $sidang->tugasAkhir,
            'dosbing1' => $sidang->tugasAkhir->dosenPembimbing1,
            'dosbing2' => $sidang->tugasAkhir->dosenPembimbing2,
            'penguji1' => $sidang->dosenPengujiKetua,
            'penguji2' => $sidang->dosenPengujiSekretaris,
            'qr_code' => true
        ];

        // Generate PDF
        $pdf = Pdf::loadView('mahasiswa.berita-acara-pdf', $data)->setPaper('a4', 'portrait');
        $content = $pdf->output();

        // Simpan & DB
        return $this->saveDocument($sidang, 'BERITA_ACARA', 'BA_Sidang_', 'berita_acara', $content);
    }

    /**
     * PRIVATE: Logic Ambil Data Penguji
     * (UPDATED: Mengambil data dari relasi detailRevisis untuk Milestone 4)
     */
    private function getDaftarPenguji($sidang)
    {
        $list = [];
        
        $addDosen = function($dosenId, $roleName) use ($sidang, &$list) {
            if (!$dosenId) return;
            
            $dosen = Dosen::find($dosenId);
            
            if ($dosen) {
                // Ambil Lembar Penilaian dosen ini beserta detail revisinya
                $nilai = $sidang->lembarPenilaians()
                                ->with('detailRevisis') // Eager load relasi baru
                                ->where('dosen_id', $dosen->id)
                                ->first();

                $list[] = [
                    'nama_dosen' => $dosen->nama_lengkap,
                    'peran' => $roleName, // Menambahkan peran untuk ditampilkan di PDF
                    // Mengirimkan collection detail revisi, bukan array string hasil explode
                    'detail_revisi' => ($nilai && $nilai->detailRevisis) ? $nilai->detailRevisis : collect([])
                ];
            }
        };

        $addDosen($sidang->dosen_penguji_ketua_id, 'Ketua Penguji');
        $addDosen($sidang->dosen_penguji_sekretaris_id, 'Sekretaris');
        $addDosen($sidang->tugasAkhir->dosen_pembimbing_1_id, 'Pembimbing 1');
        $addDosen($sidang->tugasAkhir->dosen_pembimbing_2_id, 'Pembimbing 2');

        return $list;
    }

    /**
     * PRIVATE: Simpan File & Record DB
     */
    private function saveDocument($sidang, $jenis, $prefix, $folder, $content)
    {
        // 1. Hapus File Lama
        $oldDoc = DokumenHasilSidang::where('sidang_id', $sidang->id)
                                    ->where('jenis_dokumen', $jenis)
                                    ->first();
        if ($oldDoc && Storage::disk('public')->exists($oldDoc->path_file)) {
            Storage::disk('public')->delete($oldDoc->path_file);
        }

        // 2. Signing
        $hashData = $this->signer->calculateHash($content);
        $signature = $this->signer->signWithSystemKey($hashData['raw_combined']);

        // 3. Save File
        $nrp = $sidang->tugasAkhir->mahasiswa->nrp;
        $filename = $prefix . $nrp . '_' . $sidang->id . '.pdf';
        $path = "uploads/{$folder}/" . $filename;
        Storage::disk('public')->put($path, $content);

        // 4. Save DB
        return DokumenHasilSidang::updateOrCreate(
            [
                'sidang_id' => $sidang->id,
                'jenis_dokumen' => $jenis
            ],
            [
                'path_file' => $path,
                'nama_file_asli' => $filename,
                'hash_sha512_full' => $hashData['sha512'],
                'hash_blake2b_full' => $hashData['blake2b'],
                'hash_combined' => $hashData['combined'],
                'signature_data' => $signature,
            ]
        );
    }
}