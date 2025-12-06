<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DokumenPengajuan; // Import model dokumen

class DigitalSignatureController extends Controller
{
    /**
     * Menampilkan daftar dokumen yang sudah ditandatangani.
     */
    public function index(Request $request)
    {
        // Ambil ID mahasiswa yang login
        $mahasiswaId = Auth::user()->mahasiswa_id;
        $searchQuery = $request->search;

        // Ambil SEMUA dokumen (dari SEMUA pengajuan)
        // yang dimiliki oleh mahasiswa ini
        $query = DokumenPengajuan::query()
            ->where('is_signed', true)
            ->whereHas('pengajuanSidang.tugasAkhir', function ($query) use ($mahasiswaId) {
                $query->where('mahasiswa_id', $mahasiswaId);
            });

        // Terapkan filter pencarian (jika ada)
        if ($searchQuery) {
            $query->where('nama_file_asli', 'like', '%' . $searchQuery . '%')
                  ->orWhere('tipe_dokumen', 'like', '%' . $searchQuery . '%');
        }

        $dokumenTertanda = $query->latest()->paginate(10);

        return view('mahasiswa.digital-signature', [
            'dokumenTertanda' => $dokumenTertanda,
            'searchQuery' => $searchQuery,
        ]);
    }
}