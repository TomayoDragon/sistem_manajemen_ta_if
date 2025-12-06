<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Sidang;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\DokumenHasilSidang;
use App\Services\SystemSignatureService;

class SidangController extends Controller
{
    /**
     * Menampilkan halaman jadwal Sidang / LSTA.
     */
    public function index()
    {
        // 1. Ambil TA aktif mahasiswa
        $tugasAkhir = Auth::user()->mahasiswa
            ->tugasAkhirs()
            ->latest()
            ->first();

        // 2. Jika tidak punya TA, kembalikan
        if (!$tugasAkhir) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Anda belum memiliki data Tugas Akhir.');
        }

        // 3. Ambil pengajuan terakhir untuk cek status
        $pengajuanTerbaru = $tugasAkhir->pengajuanSidangs()
            ->latest()
            ->first();

        // 4. Inisialisasi jadwal
        $lstaTerbaru = null;
        $sidangTerbaru = null;

        // 5. HANYA JIKA status = TERIMA, cari jadwalnya
        if ($pengajuanTerbaru && $pengajuanTerbaru->status_validasi == 'TERIMA') {

            $lstaTerbaru = $tugasAkhir->lstas()->latest()->first();

            // --- PERBARUI BARIS INI ---
            // Ambil jadwal Sidang terbaru DAN data Berita Acaranya (jika ada)
            $sidangTerbaru = $tugasAkhir->sidangs()
                ->with('beritaAcara') // Eager-load relasi Berita Acara
                ->latest()
                ->first();
        }

        // 6. Kirim semua data ke view
        return view('mahasiswa.sidang', [
            'pengajuanTerbaru' => $pengajuanTerbaru,
            'lsta' => $lstaTerbaru,
            'sidang' => $sidangTerbaru,
        ]);
    }

  public function downloadRevisi($id, SystemSignatureService $signer)
    {
        // Ambil Data Sidang
        $sidang = Sidang::with([
            'tugasAkhir.mahasiswa', 
            'eventSidang',          
            'lembarPenilaians.dosen'
        ])->findOrFail($id);

        // --- 1. SECURITY CHECK ---
        $mahasiswaLogin = Auth::user()->mahasiswa;
        if ($sidang->tugasAkhir->mahasiswa_id !== $mahasiswaLogin->id) {
            abort(403, 'Akses Ditolak.');
        }

        // --- 2. CEK DB: APAKAH DOKUMEN SUDAH ADA? ---
        // Kita cek di tabel baru (dokumen_hasil_sidangs)
        $existingDoc = DokumenHasilSidang::where('sidang_id', $sidang->id)
                                         ->where('jenis_dokumen', 'LEMBAR_REVISI')
                                         ->latest()
                                         ->first();

        // Jika data ada di DB DAN file fisiknya ada di storage
        if ($existingDoc && Storage::exists($existingDoc->path_file)) {
            return Storage::download($existingDoc->path_file, $existingDoc->nama_file_asli);
        }

        // --- 3. JIKA BELUM ADA, GENERATE PDF BARU ---
        // Load View PDF yang sudah kamu buat sebelumnya
        $pdf = Pdf::loadView('mahasiswa.revisi_pdf', ['sidang' => $sidang]);
        $pdf->setPaper('a4', 'portrait');
        
        // Ambil isi konten PDF (binary)
        $pdfContent = $pdf->output(); 

        // --- 4. HASHING (Pakai Service) ---
        $hashData = $signer->calculateHash($pdfContent);

        // --- 5. SIGNING (Pakai Kunci Sistem) ---
        try {
            // Sign hash gabungan (raw binary)
            $signature = $signer->signWithSystemKey($hashData['raw_combined']);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menandatangani dokumen: ' . $e->getMessage());
        }

        // --- 6. SIMPAN FILE FISIK ---
        $filename = 'Revisi_Sidang_' . $mahasiswaLogin->nrp . '_' . time() . '.pdf';
        $path = 'generated_docs/revisi/' . $filename;
        
        // Simpan ke storage/app/generated_docs/revisi/
        Storage::put($path, $pdfContent);

        // --- 7. SIMPAN DATA KE DATABASE ---
        DokumenHasilSidang::create([
            'sidang_id' => $sidang->id,
            'jenis_dokumen' => 'LEMBAR_REVISI',
            'path_file' => $path,
            'nama_file_asli' => $filename,
            // Simpan Hash
            'hash_sha512_full' => $hashData['sha512'],
            'hash_blake2b_full' => $hashData['blake2b'],
            'hash_combined' => $hashData['combined'],
            // Simpan Signature
            'signature_data' => $signature, 
        ]);

        // --- 8. DOWNLOAD ---
        return Storage::download($path, $filename);
    }
}