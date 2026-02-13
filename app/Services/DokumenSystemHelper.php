<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Sidang;
use App\Models\DokumenHasilSidang;
use App\Services\SystemSignatureService;
use App\Models\Dosen;

class DokumenSystemHelper
{
    protected $signer;

    public function __construct(SystemSignatureService $signer)
    {
        $this->signer = $signer;
    }

    /**
     * GENERATE REVISI (GABUNGAN)
     * Tambahkan parameter $forceUpdate
     */
    public function generateRevisi(Sidang $sidang, $forceUpdate = false)
    {
        $existingDoc = DokumenHasilSidang::where('sidang_id', $sidang->id)
            ->where('jenis_dokumen', 'LEMBAR_REVISI')
            ->first();

        // JIKA tidak dipaksa update DAN file sudah ada, kembalikan yang lama.
        if (!$forceUpdate && $existingDoc && Storage::disk('public')->exists($existingDoc->path_file)) {
            return $existingDoc;
        }

        // 1. REFRESH DATA: Pastikan relasi diload ulang agar keterangan_mahasiswa terbaru terbawa
        $sidang->load([
            'tugasAkhir.mahasiswa',
            'lembarPenilaians.detailRevisis'
        ]);

        $data = [
            'mahasiswa' => $sidang->tugasAkhir->mahasiswa,
            'judul' => $sidang->tugasAkhir->judul,
            'tanggal_sidang' => $sidang->jadwal,
            'qr_code' => true,
            'daftarPenguji' => $this->getDaftarPenguji($sidang)
        ];

        // 2. Generate PDF Baru
        $pdf = Pdf::loadView('mahasiswa.revisi_pdf', $data)->setPaper('a4', 'portrait');
        $content = $pdf->output();

        // 3. Simpan & Timpa (saveDocument akan melakukan updateOrCreate)
        return $this->saveDocument($sidang, 'LEMBAR_REVISI', 'Revisi_Gabungan_', 'revisi', $content);
    }

    public function getOrGenerateBeritaAcara(Sidang $sidang)
    {
        $existingDoc = DokumenHasilSidang::where('sidang_id', $sidang->id)
            ->where('jenis_dokumen', 'BERITA_ACARA')
            ->first();

        if ($existingDoc && Storage::disk('public')->exists($existingDoc->path_file)) {
            return $existingDoc;
        }

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

        $pdf = Pdf::loadView('mahasiswa.berita-acara-pdf', $data)->setPaper('a4', 'portrait');
        $content = $pdf->output();

        return $this->saveDocument($sidang, 'BERITA_ACARA', 'BA_Sidang_', 'berita_acara', $content);
    }

    private function getDaftarPenguji($sidang)
    {
        $list = [];
        $addDosen = function ($dosenId, $roleName) use ($sidang, &$list) {
            if (!$dosenId)
                return;
            $dosen = Dosen::find($dosenId);
            if ($dosen) {
                // Ambil nilai dan pastikan detail revisi terbaru terambil
                $nilai = $sidang->lembarPenilaians()
                    ->where('dosen_id', $dosen->id)
                    ->first();

                $list[] = [
                    'nama_dosen' => $dosen->nama_lengkap,
                    'peran' => $roleName,
                    'detail_revisi' => ($nilai) ? $nilai->detailRevisis : collect([])
                ];
            }
        };

        $addDosen($sidang->dosen_penguji_ketua_id, 'Ketua Penguji');
        $addDosen($sidang->dosen_penguji_sekretaris_id, 'Sekretaris');
        $addDosen($sidang->tugasAkhir->dosen_pembimbing_1_id, 'Pembimbing 1');
        $addDosen($sidang->tugasAkhir->dosen_pembimbing_2_id, 'Pembimbing 2');

        return $list;
    }

    private function saveDocument($sidang, $jenis, $prefix, $folder, $content)
    {
        // 1. Hitung Hash Terbaru menggunakan Service yang sudah diperbaiki
        // Ini mengembalikan sha512, blake2b, combined, dan binary_for_signing
        $hashData = $this->signer->calculateHash($content);

        // 2. Buat Digital Signature BARU berdasarkan hash terbaru
        $signatureBase64 = $this->signer->signWithSystemKey($hashData['binary_for_signing']);

        $nrp = $sidang->tugasAkhir->mahasiswa->nrp;
        $filename = $prefix . $nrp . '_' . $sidang->id . '.pdf';
        $path = "uploads/{$folder}/" . $filename;

        // 3. Timpa file lama di storage dengan PDF yang berisi komentar baru
        Storage::disk('public')->put($path, $content);

        // 4. Update Database dengan semua field Hash yang ada di screenshot DB kamu
        return DokumenHasilSidang::updateOrCreate(
            [
                'sidang_id' => $sidang->id,
                'jenis_dokumen' => $jenis
            ],
            [
                'path_file' => $path,
                'nama_file_asli' => $filename,
                // Sesuai field di screenshot DB dan array dari SystemSignatureService
                'hash_sha512_full' => $hashData['sha512'],
                'hash_blake2b_full' => $hashData['blake2b'],
                'hash_combined' => $hashData['combined'],
                'signature_data' => $signatureBase64,
            ]
        );
    }
}