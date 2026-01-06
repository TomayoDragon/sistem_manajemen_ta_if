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
use Illuminate\Support\Facades\DB;

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
        // 1. DEFINISI VARIABEL AWAL
        $dosenId = Auth::user()->dosen_id;
        $event = null;
        $modelClass = null;

        // 2. TENTUKAN TIPE PENILAIAN & VALIDASI AKSES
        if ($type === 'lsta') {
            $modelClass = Lsta::class;
            $event = Lsta::findOrFail($id);
            if ($event->dosen_penguji_id !== $dosenId) {
                return back()->with('error', 'Hanya Penguji LSTA yang berhak menilai.');
            }
        } elseif ($type === 'sidang') {
            $modelClass = Sidang::class;
            $event = Sidang::findOrFail($id);

            // Cek Status Sidang
            if (in_array($event->status, ['LULUS', 'TIDAK_LULUS'])) {
                return redirect()->route('dosen.dashboard')->with('error', 'Sidang selesai, penilaian terkunci.');
            }

            // Validasi Dosen Penilai
            $ta = $event->tugasAkhir;
            $allowed = [
                $ta->dosen_pembimbing_1_id,
                $event->dosen_penguji_ketua_id,
                $event->dosen_penguji_sekretaris_id
            ];
            if ($ta->dosen_pembimbing_2_id) {
                $allowed[] = $ta->dosen_pembimbing_2_id;
            }

            if (!in_array($dosenId, $allowed)) {
                abort(403, 'Anda tidak terdaftar sebagai penilai untuk sidang ini.');
            }
        } else {
            abort(404);
        }

        // 3. VALIDASI INPUT FORM
        $request->validate([
            'nilai_materi' => 'required|numeric|min:0|max:100',
            'nilai_sistematika' => 'required|numeric|min:0|max:100',
            'nilai_mempertahankan' => 'required|numeric|min:0|max:100',
            'nilai_pengetahuan_bidang' => 'required|numeric|min:0|max:100',
            'nilai_karya_ilmiah' => 'required|numeric|min:0|max:100',

            // Validasi Array Revisi (Milestone 2)
            'revisi' => 'nullable|array',
            'revisi.*' => 'required|string|max:500',
        ]);

        // 4. MULAI TRANSAKSI DATABASE
        DB::beginTransaction();
        try {
            // A. Simpan/Update Lembar Penilaian Utama
            $lembar = LembarPenilaian::updateOrCreate(
                [
                    'dosen_id' => $dosenId,
                    'penilaian_type' => $modelClass,
                    'penilaian_id' => $id
                ],
                [
                    'nilai_materi' => $request->nilai_materi,
                    'nilai_sistematika' => $request->nilai_sistematika,
                    'nilai_mempertahankan' => $request->nilai_mempertahankan,
                    'nilai_pengetahuan_bidang' => $request->nilai_pengetahuan_bidang,
                    'nilai_karya_ilmiah' => $request->nilai_karya_ilmiah,
                ]
            );

            // B. Simpan Detail Revisi (Hapus lama -> Insert baru)
            $lembar->detailRevisis()->delete();

            if ($request->has('revisi')) {
                foreach ($request->revisi as $poin) {
                    if (!empty(trim($poin))) {
                        $lembar->detailRevisis()->create([
                            'isi_revisi' => $poin,
                            'keterangan_mahasiswa' => null // Awal dibuat kosong
                        ]);
                    }
                }
            }

            // === [REVISI BARU] SIMPAN CATATAN KEJADIAN (KHUSUS SEKRETARIS) ===
            if ($type === 'sidang') {
                // Cek apakah dosen yang login adalah sekretaris
                if ($event->dosen_penguji_sekretaris_id == $dosenId) {
                    
                    // Validasi input tambahan
                    $request->validate([
                        'catatan_kejadian' => 'nullable|string|max:5000',
                    ]);
                    
                    // Update data sidang langsung
                    $event->update([
                        'catatan_kejadian' => $request->catatan_kejadian
                    ]);
                }
            }
            // ================================================================

            // C. Logika Finalisasi Nilai Akhir (Jika Perlu)
            if ($type === 'lsta') {
                // Hitung total LSTA ...
                $total = ($request->nilai_materi * 0.15) + ($request->nilai_sistematika * 0.10) +
                    ($request->nilai_mempertahankan * 0.50) + ($request->nilai_pengetahuan_bidang * 0.15) +
                    ($request->nilai_karya_ilmiah * 0.10);
                $event->status = ($total < 55) ? 'TIDAK_LULUS' : 'LULUS';
                $event->save();
            } elseif ($type === 'sidang') {
                // Logika hitung rata-rata sidang ...
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
                        $helper->getOrGenerateBeritaAcara($event); // Generate BA juga
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("PDF Error: " . $e->getMessage());
                    }
                }
            }

            DB::commit();
            return redirect()->route('dosen.dashboard')->with('success', 'Nilai dan Poin Revisi berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}