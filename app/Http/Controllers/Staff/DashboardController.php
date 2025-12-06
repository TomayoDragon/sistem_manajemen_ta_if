<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard staf (Validasi & Siap Jadwal).
     */
    public function index()
    {
        $staff = Auth::user()->staff;

        // 1. AMBIL PAKET YANG MASIH 'PENDING' (untuk tabel 1)
        $pendingPengajuans = PengajuanSidang::where('status_validasi', 'PENDING')
                                  ->with('tugasAkhir.mahasiswa')
                                  ->latest()
                                  ->get();

        // 2. AMBIL PAKET YANG 'TERIMA' TAPI BELUM PUNYA JADWAL (untuk tabel 2)
        $acceptedPengajuans = PengajuanSidang::where('status_validasi', 'TERIMA')
                                  ->where(function ($query) {
                                      // Ambil jika BELUM punya LSTA ATAU BELUM punya Sidang
                                      $query->doesntHave('lstas')
                                            ->orDoesntHave('sidangs');
                                  })
                                  ->with('tugasAkhir.mahasiswa')
                                  ->latest('validated_at')
                                  ->get();
        
        // 3. Kirim data ke view
        return view('staff.dashboard', [
            'staff' => $staff,
            'pendingPengajuans' => $pendingPengajuans,
            'acceptedPengajuans' => $acceptedPengajuans,
        ]);
    }
}