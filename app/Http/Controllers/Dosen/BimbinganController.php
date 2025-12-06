<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BimbinganController extends Controller
{
    /**
     * Menampilkan halaman "Mahasiswa Bimbingan".
     * (Method ini sudah benar, tidak perlu diubah)
     */
    public function index()
    {
        $dosenId = Auth::user()->dosen_id;

        $mahasiswaBimbingan = TugasAkhir::where(function ($query) use ($dosenId) {
            $query->where('dosen_pembimbing_1_id', $dosenId)
                ->orWhere('dosen_pembimbing_2_id', $dosenId);
        })
            ->with([
                'mahasiswa',
                'sidangs'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dosen.bimbingan', [
            'mahasiswaBimbingan' => $mahasiswaBimbingan
        ]);
    }

    /**
     * Menyetujui mahasiswa untuk lanjut ke tahap sidang.
     * (LOGIKA BARU: HANYA MENGISI KOLOM YANG SESUAI)
     */
    public function approve(TugasAkhir $tugasAkhir)
    {
        $dosenId = Auth::user()->dosen_id;

        // Cek apakah Dosen ini adalah Dosbing 1
        if ($tugasAkhir->dosen_pembimbing_1_id === $dosenId) {
            $tugasAkhir->dosbing_1_approved_at = now();
        }
        // Cek apakah Dosen ini adalah Dosbing 2
        elseif ($tugasAkhir->dosen_pembimbing_2_id === $dosenId) {
            $tugasAkhir->dosbing_2_approved_at = now();
        }
        // Jika bukan keduanya, tolak
        else {
            abort(403, 'ANDA BUKAN PEMBIMBING UNTUK MAHASISWA INI.');
        }

        // Hapus perubahan status 'ENUM' yang lama
        // $tugasAkhir->status = 'Disetujui Dosbing'; 

        $tugasAkhir->save();

        return redirect()->route('dosen.bimbingan.index')
            ->with('success', 'Persetujuan Anda telah dicatat.');
    }
}