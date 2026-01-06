<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sidang;
use App\Models\DetailRevisi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RevisiController extends Controller
{
    /**
     * Menampilkan Halaman List Revisi
     * (Nama Method Disesuaikan dengan Route: halaman_revisi)
     */
    public function halaman_revisi($sidangId) // <--- UBAH DARI index KE halaman_revisi
    {
        $mahasiswaId = Auth::user()->mahasiswa->id;

        // 1. Ambil Data Sidang & Validasi Milik Mahasiswa Ini
        $sidang = Sidang::whereHas('tugasAkhir', function($q) use ($mahasiswaId) {
            $q->where('mahasiswa_id', $mahasiswaId);
        })
        ->with([
            'tugasAkhir',
            // Load Relasi Lembar Penilaian -> Dosen & Detail Revisi
            'lembarPenilaians.dosen', 
            'lembarPenilaians.detailRevisis'
        ])
        ->findOrFail($sidangId);

        // Pastikan nama view sesuai dengan file blade Anda
        return view('mahasiswa.halaman_revisi', compact('sidang'));
    }

    /**
     * Menyimpan Keterangan/Tanggapan Mahasiswa
     */
    public function update(Request $request, $sidangId)
    {
        // Validasi input array
        $request->validate([
            'keterangan' => 'array',
            'keterangan.*' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Loop setiap input keterangan berdasarkan ID Detail Revisi
            if ($request->has('keterangan')) {
                foreach ($request->keterangan as $revisiId => $isiKeterangan) {
                    // Cari detail revisi & update
                    // Kita cari berdasarkan ID agar aman
                    $detail = DetailRevisi::find($revisiId);
                    
                    if ($detail) {
                        $detail->update([
                            'keterangan_mahasiswa' => $isiKeterangan
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Keterangan revisi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}