<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard mahasiswa.
     */
    public function index()
    {
        // 1. Ambil data user mahasiswa yang sedang login
        $mahasiswa = Auth::user()->mahasiswa;

        // 2. Ambil TA terbaru milik mahasiswa
        //    DAN ambil data relasi Dosbing & Periode
        $tugasAkhir = $mahasiswa->tugasAkhirs()
                                // --- PERUBAHAN DI SINI ---
                                ->with('dosenPembimbing1', 'dosenPembimbing2', 'periode')
                                // --- AKHIR PERUBAHAN ---
                                ->latest() // Ambil yang paling baru
                                ->first(); // Ambil 1 saja

        // 3. Kirim data ke view
        return view('mahasiswa.dashboard', [
            'mahasiswa' => $mahasiswa,
            'tugasAkhir' => $tugasAkhir, // Object $tugasAkhir sekarang berisi data ->periode
        ]);
    }
}