<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lsta;
use App\Models\Sidang;
use App\Models\LembarPenilaian;
use App\Models\BeritaAcara;
use Illuminate\Support\Facades\Auth;
use App\Services\DokumenSystemHelper;

class PenilaianController extends Controller
{
    public function show($type, $id)
    {
        $dosenId = Auth::user()->dosen_id;
        $event = null;
        $modelClass = null;

        // 1. Load Data
        if ($type === 'lsta') {
            $modelClass = Lsta::class;
            $event = Lsta::with(['tugasAkhir', 'pengajuanSidang.dokumen'])->findOrFail($id);
        } elseif ($type === 'sidang') {
            $modelClass = Sidang::class;
            $event = Sidang::with(['tugasAkhir', 'pengajuanSidang.dokumen', 'lembarPenilaians'])->findOrFail($id);
        } else {
            abort(404);
        }

        // --- [FIX 1] KUNCI FORM & REDIRECT JIKA SELESAI ---
        // Jika Berita Acara sudah terbit (Status LULUS/TIDAK), tendang ke dashboard.
        if (in_array($event->status, ['LULUS', 'TIDAK_LULUS'])) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Sidang ini telah selesai. Penilaian sudah ditutup.');
        }

        // 2. Validasi Akses
        $isAuthorized = false;
        $ta = $event->tugasAkhir;

        if ($ta->dosen_pembimbing_1_id == $dosenId) $isAuthorized = true;
        if ($ta->dosen_pembimbing_2_id && $ta->dosen_pembimbing_2_id == $dosenId) $isAuthorized = true;
        if ($type === 'lsta' && $event->dosen_penguji_id == $dosenId) $isAuthorized = true;
        if ($type === 'sidang' && ($event->dosen_penguji_ketua_id == $dosenId || $event->dosen_penguji_sekretaris_id == $dosenId)) $isAuthorized = true;

        if (!$isAuthorized) abort(403, 'AKSES DITOLAK.');

        // 3. Ambil Nilai Lama
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

    public function store(Request $request, $type, $id)
    {
        $dosenId = Auth::user()->dosen_id;
        $event = null;
        $modelClass = null;

        if ($type === 'lsta') {
            $modelClass = Lsta::class;
            $event = Lsta::findOrFail($id);
            if ($event->dosen_penguji_id !== $dosenId) return back()->with('error', 'Hanya Penguji LSTA.');
        } elseif ($type === 'sidang') {
            $modelClass = Sidang::class;
            $event = Sidang::findOrFail($id);
            
            // Cek Status Lagi
            if (in_array($event->status, ['LULUS', 'TIDAK_LULUS'])) {
                return redirect()->route('dosen.dashboard')->with('error', 'Sidang selesai, terkunci.');
            }

            $ta = $event->tugasAkhir;
            $allowed = [$ta->dosen_pembimbing_1_id, $event->dosen_penguji_ketua_id, $event->dosen_penguji_sekretaris_id];
            if ($ta->dosen_pembimbing_2_id) $allowed[] = $ta->dosen_pembimbing_2_id;

            if (!in_array($dosenId, $allowed)) abort(403, 'Invalid Penilai.');
        } else {
            abort(404);
        }

        $request->validate([
            'nilai_materi' => 'required|numeric|min:0|max:100',
            'nilai_sistematika' => 'required|numeric|min:0|max:100',
            'nilai_mempertahankan' => 'required|numeric|min:0|max:100',
            'nilai_pengetahuan_bidang' => 'required|numeric|min:0|max:100',
            'nilai_karya_ilmiah' => 'required|numeric|min:0|max:100',
            'komentar_revisi' => 'nullable|string|max:5000',
        ]);

        // Simpan Nilai
        LembarPenilaian::updateOrCreate(
            ['dosen_id' => $dosenId, 'penilaian_type' => $modelClass, 'penilaian_id' => $id],
            [
                'nilai_materi' => $request->nilai_materi,
                'nilai_sistematika' => $request->nilai_sistematika,
                'nilai_mempertahankan' => $request->nilai_mempertahankan,
                'nilai_pengetahuan_bidang' => $request->nilai_pengetahuan_bidang,
                'nilai_karya_ilmiah' => $request->nilai_karya_ilmiah,
                'komentar_revisi' => $request->komentar_revisi, 
            ]
        );

        // Finalisasi
        if ($type === 'lsta') {
            $total = ($request->nilai_materi * 0.15) + ($request->nilai_sistematika * 0.10) + 
                     ($request->nilai_mempertahankan * 0.50) + ($request->nilai_pengetahuan_bidang * 0.15) + 
                     ($request->nilai_karya_ilmiah * 0.10);
            $event->status = ($total < 55) ? 'TIDAK_LULUS' : 'LULUS';
            $event->save();
        } 
        elseif ($type === 'sidang') {
            $target = $event->tugasAkhir->dosen_pembimbing_2_id ? 4 : 3;
            if ($event->lembarPenilaians()->count() >= $target) {
                
                $semua = $event->lembarPenilaians()->get();
                $totalSemua = 0;
                foreach ($semua as $l) {
                    $totalSemua += ($l->nilai_materi * 0.15) + ($l->nilai_sistematika * 0.10) + 
                                   ($l->nilai_mempertahankan * 0.50) + ($l->nilai_pengetahuan_bidang * 0.15) + 
                                   ($l->nilai_karya_ilmiah * 0.10);
                }
                $nilaiAkhir = $totalSemua / $target;
                $status = ($nilaiAkhir >= 56) ? 'LULUS' : 'TIDAK_LULUS';
                
                $huruf = 'E';
                if ($nilaiAkhir >= 81) $huruf = 'A';
                elseif ($nilaiAkhir >= 76) $huruf = 'AB';
                elseif ($nilaiAkhir >= 66) $huruf = 'B';
                elseif ($nilaiAkhir >= 61) $huruf = 'BC';
                elseif ($nilaiAkhir >= 56) $huruf = 'C';
                elseif ($nilaiAkhir >= 41) $huruf = 'D';

                $event->update(['status' => $status, 'nilai_akhir' => $nilaiAkhir]);

                BeritaAcara::updateOrCreate(
                    ['sidang_id' => $event->id],
                    [
                        'jumlah_nilai_mentah_nma' => $totalSemua,
                        'rata_rata_nma' => $nilaiAkhir,
                        'nilai_relatif_nr' => $huruf,
                        'status' => $status,
                        'hasil_ujian' => $status,
                        'catatan' => 'Finalized System',
                    ]
                );

                try {
                    $helper = app(DokumenSystemHelper::class);
                    $helper->generateRevisi($event);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("PDF Error: " . $e->getMessage());
                }
            }
        }

        // --- [FIX 2] REDIRECT KE DASHBOARD ---
        return redirect()->route('dosen.dashboard')->with('success', 'Nilai tersimpan.');
    }
}