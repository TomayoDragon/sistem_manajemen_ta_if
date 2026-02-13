<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt; 
use Illuminate\Contracts\Encryption\DecryptException; 

use App\Models\PengajuanSidang;
use App\Models\DokumenPengajuan;
use App\Services\SignatureService;

class UploadController extends Controller
{
    public function create()
    {
        $tugasAkhir = Auth::user()->mahasiswa->tugasAkhirs()->latest()->first();

        if (!$tugasAkhir) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda belum memiliki data Tugas Akhir aktif.');
        }

        $riwayatPengajuan = $tugasAkhir->pengajuanSidangs()
            ->with('dokumen')
            ->latest()
            ->get();

        return view('mahasiswa.upload', [
            'tugasAkhir' => $tugasAkhir,
            'pengajuanTerbaru' => $riwayatPengajuan->first(),
            'riwayatPengajuan' => $riwayatPengajuan
        ]);
    }

    public function store(Request $request, SignatureService $signatureService)
    {
        $mahasiswa = Auth::user()->mahasiswa;

        // =========================================================================
        // 1. CEK KEY & AUTO-HEALING (Tetap dipertahankan untuk Digital Signature)
        // =========================================================================
        $keyIsValid = false;

        if (!empty($mahasiswa->private_key_encrypted)) {
            try {
                Crypt::decryptString($mahasiswa->private_key_encrypted);
                $keyIsValid = true;
            } catch (\Exception $e) {
                $keyIsValid = false; 
            }
        }

        if (!$keyIsValid) {
            try {
                $signatureService->generateAndStoreKeys($mahasiswa);
                $mahasiswa = $mahasiswa->fresh(); 
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Gagal generate key: ' . $e->getMessage());
            }
        }

        // =========================================================================
        // 2. VALIDASI INPUT
        // =========================================================================
        $request->validate([
            // REVISI DOSEN: File harus dokumen (doc/docx/pdf) agar bisa dibuka langsung, bukan ZIP.
            // REVISI DOSEN: Tambahan validasi untuk Link Google Docs
            'naskah_ta'       => 'required|file|mimes:doc,docx,pdf|max:51200', 
            'link_naskah_ta'  => 'required|url', 

            // Validasi file pendukung lainnya
            'proposal_ta'     => 'required|file|mimes:pdf|max:10240',
            'artikel_jurnal'  => 'required|file|mimes:pdf|max:10240',
            'kartu_studi'     => 'required|file|mimes:pdf|max:5120',
            'surat_tugas'     => 'required|file|mimes:pdf|max:5120',
            'bukti_bimbingan' => 'required|file|mimes:pdf|max:10240',
            'sertifikat_lsta' => 'required|file|mimes:pdf|max:5120',
            'bukti_persetujuan' => 'required|file|mimes:pdf|max:5120',
            'video_promosi'   => 'required|file|mimes:mp4|max:102400',
        ]);

        $tugasAkhir = $mahasiswa->tugasAkhirs()->latest()->first();

        if ($tugasAkhir->pengajuanSidangs()->where('status_validasi', 'PENDING')->exists()) {
            return redirect()->back()->with('error', 'Anda masih memiliki pengajuan yang sedang diverifikasi.');
        }

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanSidang::create([
                'tugas_akhir_id' => $tugasAkhir->id,
                'status_validasi' => 'PENDING',
            ]);

            $filesToProcess = [
                'naskah_ta'         => 'NASKAH_TA',
                'proposal_ta'       => 'PROPOSAL_TA',
                'artikel_jurnal'    => 'ARTIKEL_JURNAL',
                'kartu_studi'       => 'KARTU_STUDI',
                'surat_tugas'       => 'SURAT_TUGAS',
                'bukti_bimbingan'   => 'BUKTI_BIMBINGAN',
                'sertifikat_lsta'   => 'SERTIFIKAT_LSTA',
                'bukti_persetujuan' => 'BUKTI_PERSETUJUAN',
                'video_promosi'     => 'VIDEO_PROMOSI',
            ];

            foreach ($filesToProcess as $inputName => $dbType) {
                if (!$request->hasFile($inputName)) continue;
                
                $file = $request->file($inputName);
                
                // =============================================================
                // PROSES CORE SKRIPSI (HASHING & SIGNING)
                // Tetap menggunakan file fisik sebagai acuan integritas data
                // =============================================================
                
                // 1. Hashing (Custom Hash SHA512 + BLAKE2b)
                $hashData = $signatureService->performCustomHash($file->get()); 

                // 2. Signing (EdDSA)
                $signature = $signatureService->performRealEdDSASigning(
                    $hashData['combined_raw_for_signing'], 
                    $mahasiswa 
                );

                // 3. Simpan File Fisik
                $extension = $file->getClientOriginalExtension();
                $customName = $mahasiswa->nrp . '_' . $this->getFileNameByType($dbType) . '.' . $extension;
                $path = $file->storeAs('uploads/dokumen_pengajuan', $customName);

                // =============================================================
                // LOGIKA KHUSUS REVISI DOSEN (LINK GOOGLE DOCS)
                // =============================================================
                $externalLink = null;
                // Cek jika ini adalah Naskah TA, ambil link dari input form
                if ($dbType === 'NASKAH_TA') {
                    $externalLink = $request->input('link_naskah_ta');
                }

                DokumenPengajuan::create([
                    'pengajuan_sidang_id' => $pengajuan->id,
                    'tipe_dokumen' => $dbType,
                    'path_penyimpanan' => $path,
                    'nama_file_asli' => $customName,
                    'hash_sha512_full' => $hashData['sha512_full_hex'],
                    'hash_blake2b_full' => $hashData['blake2b_full_hex'],
                    'hash_combined' => $hashData['combined_hex'],
                    'signature_data' => $signature,
                    'is_signed' => true,
                    // Simpan link jika ada (hanya untuk NASKAH_TA)
                    'external_link' => $externalLink, 
                ]);
            }

            DB::commit();
            return redirect()->route('mahasiswa.upload')->with('success', 'Berhasil upload, tanda tangan digital, dan menyimpan link dokumen.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('mahasiswa.upload')->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    private function getFileNameByType($type) {
        return match($type) {
            'NASKAH_TA'         => 'NaskahTA',
            'PROPOSAL_TA'       => 'ProposalTA',
            'ARTIKEL_JURNAL'    => 'ArtikelJurnalTA',
            'KARTU_STUDI'       => 'KS',
            'SURAT_TUGAS'       => 'ST',
            'BUKTI_BIMBINGAN'   => 'BuktiBimbingan',
            'SERTIFIKAT_LSTA'   => 'LSTA',
            'BUKTI_PERSETUJUAN' => 'PersetujuanDosbing',
            'VIDEO_PROMOSI'     => 'VideoPromosi',
            default             => 'Dokumen',
        };
    }
}

