<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sidang;
use App\Models\DetailRevisi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// --- IMPORT HELPER ---
use App\Services\DokumenSystemHelper;

class RevisiController extends Controller
{
    protected $docHelper;

    // Tambahkan constructor agar Helper bisa digunakan
    public function __construct(DokumenSystemHelper $docHelper)
    {
        $this->docHelper = $docHelper;
    }

    public function halaman_revisi($sidangId)
    {
        $mahasiswaId = Auth::user()->mahasiswa->id;

        $sidang = Sidang::whereHas('tugasAkhir', function($q) use ($mahasiswaId) {
            $q->where('mahasiswa_id', $mahasiswaId);
        })
        ->with([
            'tugasAkhir',
            'lembarPenilaians.dosen', 
            'lembarPenilaians.detailRevisis'
        ])
        ->findOrFail($sidangId);

        return view('mahasiswa.halaman_revisi', compact('sidang'));
    }

    public function update(Request $request, $sidangId)
    {
        $request->validate([
            'keterangan' => 'array',
            'keterangan.*' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            if ($request->has('keterangan')) {
                foreach ($request->keterangan as $revisiId => $isiKeterangan) {
                    $detail = DetailRevisi::find($revisiId);
                    if ($detail) {
                        $detail->update([
                            'keterangan_mahasiswa' => $isiKeterangan
                        ]);
                    }
                }
            }

            DB::commit();

            // --- BAGIAN KRUSIAL: GENERATE PDF SETELAH COMMIT ---
            // Ambil data sidang terbaru
            $sidang = Sidang::find($sidangId);
            
            // Panggil helper untuk menimpa file PDF lama dengan data baru
            // Parameter 'true' memaksa sistem mengabaikan cache file lama
            $this->docHelper->generateRevisi($sidang, true);

            return redirect()->back()->with('success', 'Keterangan revisi berhasil disimpan dan dokumen PDF telah diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}