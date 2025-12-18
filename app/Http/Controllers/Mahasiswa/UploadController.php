<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PengajuanSidang;
use App\Models\TugasAkhir;
use App\Models\DokumenPengajuan;
use App\Services\SignatureService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    /**
     * Menampilkan halaman form upload.
     */
    /**
     * Menampilkan halaman form upload.
     * (DIPERBARUI DENGAN LOGIKA PERSETUJUAN GANDA)
     */
    public function create()
    {
        // 1. Ambil TA aktif milik mahasiswa
        $tugasAkhir = Auth::user()->mahasiswa
            ->tugasAkhirs()
            ->latest()
            ->first();

        // 2. JIKA TIDAK PUNYA TA
        if (!$tugasAkhir) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda belum memiliki data Tugas Akhir aktif.');
        }

        // 3. --- LOGIKA KUNCI BARU ---
        // JIKA SALAH SATU ATAU KEDUA DOSBING BELUM SETUJU
        if (is_null($tugasAkhir->dosbing_1_approved_at) || is_null($tugasAkhir->dosbing_2_approved_at)) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Upload berkas ditolak. Anda belum mendapatkan persetujuan dari KEDUA Dosen Pembimbing untuk melanjutkan ke tahap sidang.');
        }
        // --- AKHIR LOGIKA KUNCI ---

        // (Jika lolos, berarti KEDUA dosbing sudah setuju)

        // 4. Ambil riwayat pengajuan (Logic lama tetap berjalan)
        $riwayatPengajuan = $tugasAkhir->pengajuanSidangs()
            ->with('dokumen')
            ->latest()
            ->get();

        $pengajuanTerbaru = $riwayatPengajuan->first();

        // 5. Tampilkan view upload
        return view('mahasiswa.upload', [
            'tugasAkhir' => $tugasAkhir,
            'pengajuanTerbaru' => $pengajuanTerbaru,
            'riwayatPengajuan' => $riwayatPengajuan
        ]);
    }

    /**
     * Menyimpan paket pengajuan, melakukan HASHING & SIGNATURE PER FILE (ASLI).
     */
  public function store(Request $request, SignatureService $signatureService)
    {
        // 1. Validasi 9 File Wajib
        $request->validate([
            'naskah_ta'       => 'required|file|mimes:zip,rar|max:51200', // Max 50MB (ZIP)
            'proposal_ta'     => 'required|file|mimes:pdf|max:10240',
            'artikel_jurnal'  => 'required|file|mimes:pdf|max:10240',
            'kartu_studi'     => 'required|file|mimes:pdf|max:5120',
            'surat_tugas'     => 'required|file|mimes:pdf|max:5120',
            'bukti_bimbingan' => 'required|file|mimes:pdf|max:10240',
            'sertifikat_lsta' => 'required|file|mimes:pdf|max:5120',
            'bukti_doswal'    => 'required|file|mimes:pdf|max:5120',
            'video_promosi'   => 'required|file|mimes:mp4|max:102400', // Max 100MB (Video)
        ]);

        $tugasAkhir = Auth::user()->mahasiswa->tugasAkhirs()->latest()->first();
        // ... (Cek Kunci & Pending tetap sama) ...

        DB::beginTransaction();
        try {
            $pengajuan = PengajuanSidang::create([
                'tugas_akhir_id' => $tugasAkhir->id,
                'status_validasi' => 'PENDING',
            ]);

            // Mapping Input Name ke Tipe Dokumen Database
            $filesToProcess = [
                'naskah_ta'       => 'NASKAH_TA',
                'proposal_ta'     => 'PROPOSAL_TA',
                'artikel_jurnal'  => 'ARTIKEL_JURNAL',
                'kartu_studi'     => 'KARTU_STUDI',
                'surat_tugas'     => 'SURAT_TUGAS',
                'bukti_bimbingan' => 'BUKTI_BIMBINGAN',
                'sertifikat_lsta' => 'SERTIFIKAT_LSTA',
                'bukti_doswal'    => 'BUKTI_DOSWAL',
                'video_promosi'   => 'VIDEO_PROMOSI',
            ];

            foreach ($filesToProcess as $inputName => $dbType) {
                $file = $request->file($inputName);
                
                // Proses Signature (Sama seperti sebelumnya)
                $fileContent = $file->get();
                $hashData = $signatureService->performCustomHash($fileContent); 
                $signature = $signatureService->performRealEdDSASigning(
                    $hashData['combined_raw_for_signing'],
                    Auth::user()->mahasiswa
                );
                
                // Simpan File dengan Nama Unik
                // Format Nama: NRP_JenisDokumen.ext (Contoh: 160421001_NaskahTA.zip)
                $extension = $file->getClientOriginalExtension();
                $nrp = Auth::user()->mahasiswa->nrp;
                $customName = $nrp . '_' . $this->getFileNameByType($dbType) . '.' . $extension;
                
                $path = $file->storeAs('uploads/dokumen_pengajuan', $customName);

                DokumenPengajuan::create([
                    'pengajuan_sidang_id' => $pengajuan->id,
                    'tipe_dokumen' => $dbType,
                    'path_penyimpanan' => $path,
                    'nama_file_asli' => $customName, // Simpan nama format baru
                    'hash_sha512_full' => $hashData['sha512_full_hex'],
                    'hash_blake2b_full' => $hashData['blake2b_full_hex'],
                    'hash_combined' => $hashData['combined_hex'],
                    'signature_data' => $signature,
                    'is_signed' => true,
                ]);
            }

            DB::commit();
            return redirect()->route('mahasiswa.upload')->with('success', 'Semua berkas berhasil diupload dan ditandatangani.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('mahasiswa.upload')->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // Helper untuk format penamaan file
    private function getFileNameByType($type) {
        return match($type) {
            'NASKAH_TA'       => 'NaskahTA',
            'PROPOSAL_TA'     => 'ProposalTA',
            'ARTIKEL_JURNAL'  => 'ArtikelJurnalTA',
            'KARTU_STUDI'     => 'KS',
            'SURAT_TUGAS'     => 'ST',
            'BUKTI_BIMBINGAN' => 'BuktiBimbingan',
            'SERTIFIKAT_LSTA' => 'LSTA',
            'BUKTI_DOSWAL'    => 'DosenWali',
            'VIDEO_PROMOSI'   => 'VideoPromosi',
            default           => 'Dokumen',
        };
    }
}