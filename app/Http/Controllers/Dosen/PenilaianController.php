<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lsta;
use App\Models\Sidang;
use App\Models\LembarPenilaian;
use Illuminate\Support\Facades\Auth;
use App\Services\BeritaAcaraService;

class PenilaianController extends Controller
{
    /**
     * Menampilkan form lembar penilaian.
     */
    public function show($type, $id)
    {
        $dosenId = Auth::user()->dosen_id;
        $event = null;
        $modelClass = null;

        // 1. Load Event & Relasi
        if ($type === 'lsta') {
            $modelClass = Lsta::class;
            $event = Lsta::with([
                'tugasAkhir.mahasiswa', 
                'pengajuanSidang.dokumen',
                'tugasAkhir' // Load TA untuk cek pembimbing
            ])->findOrFail($id);
        } elseif ($type === 'sidang') {
            $modelClass = Sidang::class;
            $event = Sidang::with([
                'tugasAkhir.mahasiswa', 
                'pengajuanSidang.dokumen',
                'tugasAkhir' // Load TA untuk cek pembimbing
            ])->findOrFail($id);
        } else {
            abort(404);
        }

        // 2. --- LOGIKA KEAMANAN (BARU) ---
        // Cek apakah dosen yang login berhak melihat halaman ini
        $isAuthorized = false;
        $ta = $event->tugasAkhir;

        // Cek Pembimbing (Berlaku untuk LSTA & Sidang)
        if ($ta->dosen_pembimbing_1_id == $dosenId || $ta->dosen_pembimbing_2_id == $dosenId) {
            $isAuthorized = true;
        }

        // Cek Penguji (Spesifik per tipe)
        if ($type === 'lsta') {
            if ($event->dosen_penguji_id == $dosenId) {
                $isAuthorized = true;
            }
        } elseif ($type === 'sidang') {
            if ($event->dosen_penguji_ketua_id == $dosenId || $event->dosen_penguji_sekretaris_id == $dosenId) {
                $isAuthorized = true;
            }
        }

        // Jika tidak punya hak akses, tolak!
        if (!$isAuthorized) {
            abort(403, 'AKSES DITOLAK. Anda bukan Dosen Pembimbing atau Penguji untuk jadwal ini.');
        }
        // --- AKHIR LOGIKA KEAMANAN ---


        // 3. Ambil Nilai yang Sudah Ada (Jika ada)
        $existingScore = LembarPenilaian::where('dosen_id', $dosenId)
                            ->where('penilaian_type', $modelClass)
                            ->where('penilaian_id', $event->id)
                            ->first();

        return view('dosen.penilaian', [
            'event' => $event,
            'type' => $type,
            'existingScore' => $existingScore
        ]);
    }

    /**
     * Menyimpan nilai DAN MENTRIGGER FINALISASI OTOMATIS.
     */
    public function store(Request $request, $type, $id, BeritaAcaraService $baService)
    {
        $dosenId = Auth::user()->dosen_id;
        $event = null;
        $modelClass = null;

        if ($type === 'lsta') {
            $modelClass = Lsta::class;
            $event = Lsta::findOrFail($id);
            
            // Validasi Khusus LSTA: Hanya Penguji yang boleh menilai
            if ($event->dosen_penguji_id !== $dosenId) {
                return redirect()->back()->with('error', 'Hanya Dosen Penguji yang berhak mengisi nilai LSTA.');
            }
            
        } elseif ($type === 'sidang') {
            $modelClass = Sidang::class;
            $event = Sidang::findOrFail($id);

            // Validasi Khusus Sidang: Hanya 4 orang yang boleh menilai
            $ta = $event->tugasAkhir;
            $allowedDosen = [
                $ta->dosen_pembimbing_1_id,
                $ta->dosen_pembimbing_2_id,
                $event->dosen_penguji_ketua_id,
                $event->dosen_penguji_sekretaris_id
            ];

            if (!in_array($dosenId, $allowedDosen)) {
                 abort(403, 'Anda tidak terdaftar sebagai penilai sidang ini.');
            }
        } else {
            abort(404);
        }

        $request->validate([
            'nilai_materi' => 'required|integer|min:0|max:100',
            'nilai_sistematika' => 'required|integer|min:0|max:100',
            'nilai_mempertahankan' => 'required|integer|min:0|max:100',
            'nilai_pengetahuan_bidang' => 'required|integer|min:0|max:100',
            'nilai_karya_ilmiah' => 'required|integer|min:0|max:100',
            'komentar_revisi' => 'nullable|string|max:2000',
        ]);

        // Simpan Nilai
        $lembar = LembarPenilaian::updateOrCreate(
            [
                'dosen_id' => $dosenId,
                'penilaian_type' => $modelClass,
                'penilaian_id' => $id,
            ],
            [
                'nilai_materi' => $request->input('nilai_materi'),
                'nilai_sistematika' => $request->input('nilai_sistematika'),
                'nilai_mempertahankan' => $request->input('nilai_mempertahankan'),
                'nilai_pengetahuan_bidang' => $request->input('nilai_pengetahuan_bidang'),
                'nilai_karya_ilmiah' => $request->input('nilai_karya_ilmiah'),
                'komentar_revisi' => $request->input('komentar_revisi'),
            ]
        );

        // Cek Trigger Selesai
        $event->load('lembarPenilaians');
        $jumlahNilaiMasuk = $event->lembarPenilaians->count();

        if ($type === 'lsta') {
            // LSTA Selesai jika 1 Dosen (Penguji) sudah menilai
            if ($jumlahNilaiMasuk >= 1) { 
                $nilaiAkhir = ($lembar->nilai_materi * 0.15) + 
                              ($lembar->nilai_sistematika * 0.10) + 
                              ($lembar->nilai_mempertahankan * 0.50) + 
                              ($lembar->nilai_pengetahuan_bidang * 0.15) + 
                              ($lembar->nilai_karya_ilmiah * 0.10);

                $event->status = ($nilaiAkhir < 55) ? 'TIDAK_LULUS' : 'LULUS';
                $event->save();
            }
        
        } elseif ($type === 'sidang') {
            // SIDANG: Butuh 4 Penilai
            if ($jumlahNilaiMasuk == 4) {
                $baService->generate($event);
            }
        }
        
        return redirect()->route('dosen.dashboard')
            ->with('success', 'Lembar penilaian berhasil disimpan.');
    }
}