<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSidang;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; 

class ValidasiController extends Controller
{
    public function show($id)
    {
        $pengajuan = PengajuanSidang::with('tugasAkhir.mahasiswa', 'dokumen')
                        ->where('id', $id)
                        ->firstOrFail(); 

        return view('staff.review', [
            'pengajuan' => $pengajuan
        ]);
    }

    public function process(Request $request, $id) 
    {
        // 1. Validasi input
        $request->validate([
            'keputusan' => 'required|in:TERIMA,TOLAK',
            'catatan_validasi' => 'required_if:keputusan,TOLAK|nullable|string|max:1000',
        ]);

        $pengajuan = PengajuanSidang::with(['tugasAkhir', 'dokumen'])->findOrFail($id);

        // =====================================================================
        // SKENARIO A: JIKA DITOLAK -> HAPUS SEMUA DATA
        // =====================================================================
        if ($request->input('keputusan') == 'TOLAK') {
            
            DB::beginTransaction();
            try {
                foreach ($pengajuan->dokumen as $doc) {
                    if ($doc->path_penyimpanan && Storage::exists($doc->path_penyimpanan)) {
                        Storage::delete($doc->path_penyimpanan);
                    }
                }
                $pengajuan->dokumen()->delete();
                $pengajuan->delete();

                DB::commit();

                return redirect()->route('staff.dashboard')
                    ->with('error', 'Pengajuan DITOLAK. Seluruh berkas telah dihapus.');

            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
            }
        }

        // =====================================================================
        // SKENARIO B: JIKA DITERIMA -> UPDATE STATUS
        // =====================================================================
        if ($request->input('keputusan') == 'TERIMA') {
            
            $pengajuan->status_validasi = 'TERIMA'; 
            $pengajuan->catatan_validasi = $request->input('catatan_validasi');
            
            $pengajuan->validator_id = Auth::user()->staff->id; 

            $pengajuan->validated_at = now();
            $pengajuan->save();

            // Update Status Tugas Akhir
            $tugasAkhir = $pengajuan->tugasAkhir; 
            if ($tugasAkhir) {
                $tugasAkhir->status = 'Menunggu Sidang'; 
                $tugasAkhir->save();
            }

            return redirect()->route('staff.dashboard')
                ->with('success', 'Paket pengajuan berhasil DISETUJUI.');
        }
    }
}