<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\TugasAkhir;
use App\Models\DokumenPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BimbinganController extends Controller
{
    public function index()
    {
        $dosenId = Auth::user()->dosen_id;

        $mahasiswaBimbingan = TugasAkhir::where(function ($query) use ($dosenId) {
            $query->where('dosen_pembimbing_1_id', $dosenId)
                ->orWhere('dosen_pembimbing_2_id', $dosenId);
        })
        // PERBAIKAN DISINI: Gunakan closure untuk memfilter relasi
        ->with(['mahasiswa', 'sidangs', 'pengajuanSidangs' => function($q) {
            $q->latest(); // Mengurutkan dari yang paling baru dibuat
        }])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('dosen.bimbingan', [
            'mahasiswaBimbingan' => $mahasiswaBimbingan
        ]);
    }

    public function show($id)
    {
        $dosenId = Auth::user()->dosen_id;

        $tugasAkhir = TugasAkhir::where('id', $id)
            ->where(function ($query) use ($dosenId) {
                $query->where('dosen_pembimbing_1_id', $dosenId)
                      ->orWhere('dosen_pembimbing_2_id', $dosenId);
            })
            ->firstOrFail();

        // PERBAIKAN DISINI: Ambil pengajuan TERBARU secara manual agar akurat
        // Jangan mengandalkan eager loading ->first() dari parent karena bisa salah urutan
        $pengajuanTerbaru = $tugasAkhir->pengajuanSidangs()
            ->with('dokumen') // Load dokumennya sekalian
            ->latest() // Order by created_at desc
            ->first(); // Ambil satu saja yang paling atas (terbaru)

        return view('dosen.detail-berkas', [
            'tugasAkhir' => $tugasAkhir,
            'pengajuan' => $pengajuanTerbaru
        ]);
    }

    public function viewDokumen($id)
    {
        $dokumen = DokumenPengajuan::findOrFail($id);

        // LOGIKA BARU: Jika link ada (tidak null DAN tidak kosong), redirect
        if (!empty($dokumen->external_link)) {
            return redirect()->away($dokumen->external_link);
        }

        if (!Storage::exists($dokumen->path_penyimpanan)) {
            return back()->with('error', 'File fisik tidak ditemukan.');
        }

        $mimeType = Storage::mimeType($dokumen->path_penyimpanan);

        return Storage::response($dokumen->path_penyimpanan, $dokumen->nama_file_asli, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $dokumen->nama_file_asli . '"',
        ]);
    }
    
   
}