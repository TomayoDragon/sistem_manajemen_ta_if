<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard staf.
     * HANYA MENAMPILKAN ANTRIAN VALIDASI (PENDING).
     * (Fitur penjadwalan sudah dipindah ke menu 'Atur Jadwal')
     */
    public function index()
    {
        $staff = Auth::user()->staff;

        // 1. AMBIL PAKET YANG MASIH 'PENDING'
        // Hanya ini yang dibutuhkan di Dashboard sekarang.
        $pendingPengajuans = PengajuanSidang::where('status_validasi', 'PENDING')
            ->with('tugasAkhir.mahasiswa')
            ->latest()
            ->get();

        // BAGIAN $acceptedPengajuans SUDAH DIHAPUS 
        // Agar tidak error relasi dan tidak memberatkan query database.

        return view('staff.dashboard', [
            'staff' => $staff,
            'pendingPengajuans' => $pendingPengajuans,
        ]);
    }
}