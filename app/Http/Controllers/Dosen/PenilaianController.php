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
use Illuminate\Support\Facades\Log;

class PenilaianController extends Controller
{
    public function show($type, $id)
    {
        $dosenId = Auth::user()->dosen_id;
        $event = null;
        $modelClass = null;

        if ($type === 'lsta') {
            $modelClass = Lsta::class;
            $event = Lsta::with(['tugasAkhir', 'pengajuanSidang.dokumen'])->findOrFail($id);
        } elseif ($type === 'sidang') {
            $modelClass = Sidang::class;
            $event = Sidang::with(['tugasAkhir', 'pengajuanSidang.dokumen', 'lembarPenilaians'])->findOrFail($id);
        } else {
            abort(404);
        }

        // Kunci form jika sidang sudah final
        if (in_array($event->status, ['LULUS', 'TIDAK_LULUS'])) {
            return redirect()->route('dosen.dashboard')
                ->with('error', 'Sidang ini telah selesai. Penilaian sudah ditutup.');
        }

        // Validasi Akses
        $isAuthorized = false;
        $ta = $event->tugasAkhir;

        if ($ta->dosen_pembimbing_1_id == $dosenId) $isAuthorized = true;
        if ($ta->dosen_pembimbing_2_id && $ta->dosen_pembimbing_2_id == $dosenId) $isAuthorized = true;
        if ($type === 'lsta' && $event->dosen_penguji_id == $dosenId) $isAuthorized = true;
        if ($type === 'sidang' && ($event->dosen_penguji_ketua_id == $dosenId || $event->dosen_penguji_sekretaris_id == $dosenId)) $isAuthorized = true;

        if (!$isAuthorized) abort(403, 'AKSES DITOLAK.');

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
            if ($event->dosen_penguji_id !== $dosenId) {
                return back()->with('error', 'Hanya Penguji LSTA yang berhak menilai.');
            }
        } elseif ($type === 'sidang') {
            $modelClass = Sidang::class;
            $event = Sidang::findOrFail($id);

            if (in_array($event->status, ['LULUS', 'TIDAK_LULUS'])) {
                return redirect()->route('dosen.dashboard')->with('error', 'Sidang selesai, penilaian terkunci.');
            }

            $ta = $event->tugasAkhir;
            $allowed = [$ta->dosen_pembimbing_1_id, $event->dosen_penguji_ketua_id, $event->dosen_penguji_sekretaris_id];
            if ($ta->dosen_pembimbing_2_id) $allowed[] = $ta->dosen_pembimbing_2_id;

            if (!in_array($dosenId, $allowed)) abort(403, 'Akses Ditolak.');
        } else {
            abort(404);
        }

        $request->validate([
            'nilai_materi' => 'required|numeric|min:0|max:100',
            'nilai_sistematika' => 'required|numeric|min:0|max:100',
            'nilai_mempertahankan' => 'required|numeric|min:0|max:100',
            'nilai_pengetahuan_bidang' => 'required|numeric|min:0|max:100',
            'nilai_karya_ilmiah' => 'required|numeric|min:0|max:100',
            'revisi' => 'nullable|array',
            'revisi.*' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // A. Simpan Lembar Penilaian
            $lembar = LembarPenilaian::updateOrCreate(
                ['dosen_id' => $dosenId, 'penilaian_type' => $modelClass, 'penilaian_id' => $id],
                [
                    'nilai_materi' => $request->nilai_materi,
                    'nilai_sistematika' => $request->nilai_sistematika,
                    'nilai_mempertahankan' => $request->nilai_mempertahankan,
                    'nilai_pengetahuan_bidang' => $request->nilai_pengetahuan_bidang,
                    'nilai_karya_ilmiah' => $request->nilai_karya_ilmiah,
                ]
            );

            // B. Simpan Detail Revisi
            $lembar->detailRevisis()->delete();
            if ($request->has('revisi')) {
                foreach ($request->revisi as $poin) {
                    if (!empty(trim($poin))) {
                        $lembar->detailRevisis()->create(['isi_revisi' => $poin]);
                    }
                }
            }

            // C. Khusus Sekretaris: Simpan Catatan Kejadian
            if ($type === 'sidang' && $event->dosen_penguji_sekretaris_id == $dosenId) {
                $event->update(['catatan_kejadian' => $request->catatan_kejadian]);
            }

            // D. Logika Finalisasi (Jika semua dosen sudah menilai)
            if ($type === 'lsta') {
                $total = ($request->nilai_materi * 0.15) + ($request->nilai_sistematika * 0.10) + ($request->nilai_mempertahankan * 0.50) + ($request->nilai_pengetahuan_bidang * 0.15) + ($request->nilai_karya_ilmiah * 0.10);
                $event->status = ($total < 55) ? 'TIDAK_LULUS' : 'LULUS';
                $event->save();
            } elseif ($type === 'sidang') {
                $target = $event->tugasAkhir->dosen_pembimbing_2_id ? 4 : 3;
                if ($event->lembarPenilaians()->count() >= $target) {
                    $semua = $event->lembarPenilaians()->get();
                    $totalSemua = 0;
                    foreach ($semua as $l) {
                        $totalSemua += ($l->nilai_materi * 0.15) + ($l->nilai_sistematika * 0.10) + ($l->nilai_mempertahankan * 0.50) + ($l->nilai_pengetahuan_bidang * 0.15) + ($l->nilai_karya_ilmiah * 0.10);
                    }
                    $nilaiAkhir = $totalSemua / $target;
                    $status = ($nilaiAkhir >= 56) ? 'LULUS' : 'TIDAK_LULUS';
                    
                    // Tentukan Huruf
                    $huruf = 'E';
                    if ($nilaiAkhir >= 81) $huruf = 'A';
                    elseif ($nilaiAkhir >= 76) $huruf = 'AB';
                    elseif ($nilaiAkhir >= 66) $huruf = 'B';
                    elseif ($nilaiAkhir >= 61) $huruf = 'BC';
                    elseif ($nilaiAkhir >= 56) $huruf = 'C';

                    // Update Sidang
                    $event->update(['status' => $status, 'nilai_akhir' => $nilaiAkhir]);

                    // Buat Berita Acara
                    BeritaAcara::updateOrCreate(
                        ['sidang_id' => $event->id],
                        [
                            'jumlah_nilai_mentah_nma' => $totalSemua,
                            'rata_rata_nma' => $nilaiAkhir,
                            'nilai_relatif_nr' => $huruf,
                            'status' => $status,
                            'hasil_ujian' => $status,
                            'catatan' => 'Finalized by System',
                        ]
                    );

                    // --- [LOGIKA BARU] UPDATE STATUS TUGAS AKHIR KE SELESAI ---
                    if ($status === 'LULUS') {
                        $event->tugasAkhir->update(['status' => 'Selesai']);
                    }

                    // Generate Dokumen (Digital Signature & PDF)
                    try {
                        $helper = app(DokumenSystemHelper::class);
                        $helper->generateRevisi($event); // Signature 1
                        $helper->getOrGenerateBeritaAcara($event); // Signature 2
                    } catch (\Exception $e) {
                        Log::error("Digital Signature/PDF Error: " . $e->getMessage());
                    }
                }
            }

            DB::commit();
            return redirect()->route('dosen.dashboard')->with('success', 'Penilaian berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }
}