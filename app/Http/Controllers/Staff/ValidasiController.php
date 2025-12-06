<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSidang;
use IlluminateS\upport\Facades\Request; // <-- Perhatikan, saya ganti Request
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ValidasiController extends Controller
{
    /**
     * Menampilkan halaman detail "Review Paket Pengajuan".
     */
    public function show($id)
    {
        // Ambil data pengajuan DAN relasi 'dokumen' & 'tugasAkhir'
        $pengajuan = PengajuanSidang::with('tugasAkhir.mahasiswa', 'dokumen')
                        ->where('id', $id)
                        ->where('status_validasi', 'PENDING')
                        ->firstOrFail(); 

        return view('staff.review', [
            'pengajuan' => $pengajuan
        ]);
    }

    /**
     * Memproses keputusan validasi (Terima / Tolak).
     * (INI ADALAH FUNGSI YANG DIPERBAIKI)
     */
    public function process(\Illuminate\Http\Request $request, $id) // <-- Perhatikan, saya tambahkan \Illuminate\Http\Request
    {
        // 1. Validasi input
        $request->validate([
            'keputusan' => 'required|in:TERIMA,TOLAK',
            'catatan_validasi' => 'required_if:keputusan,TOLAK|nullable|string|max:1000',
        ]);

        // 2. Ambil data pengajuan (termasuk relasi TA)
        $pengajuan = PengajuanSidang::with('tugasAkhir')->findOrFail($id);

        // 3. Update status pengajuan
        $pengajuan->status_validasi = $request->input('keputusan');
        $pengajuan->catatan_validasi = $request->input('catatan_validasi');
        $pengajuan->validator_id = Auth::user()->staff_id;
        $pengajuan->validated_at = now();
        $pengajuan->save();

        // 4. --- LOGIKA BARU (Sesuai Permintaan Anda) ---
        // Jika keputusannya 'TERIMA', update status utama di tabel Tugas Akhir
        if ($request->input('keputusan') == 'TERIMA') {
            
            // Ambil relasi tugasAkhir
            $tugasAkhir = $pengajuan->tugasAkhir; 
            
            // Update status utama
            if ($tugasAkhir) {
                $tugasAkhir->status = 'Menunggu Sidang'; // Status baru
                $tugasAkhir->save();
            }
        }
        // --- AKHIR LOGIKA BARU ---

        // 5. Redirect kembali
        $pesan = $request->input('keputusan') == 'TERIMA' ? 'disetujui' : 'ditolak';
        
        return redirect()->route('staff.dashboard')
            ->with('success', 'Paket pengajuan berhasil ' . $pesan . '.');
    }
}