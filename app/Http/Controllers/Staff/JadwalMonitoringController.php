<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lsta;
use App\Models\Sidang;

class JadwalMonitoringController extends Controller
{
    public function index()
    {
        // 1. Ambil Data LSTA
        $lstas = Lsta::with([
            'tugasAkhir.mahasiswa',
            'tugasAkhir.dosenPembimbing1',
            'tugasAkhir.dosenPembimbing2',
            'dosenPenguji',
            'eventSidang.periode'
        ])->whereHas('eventSidang.periode', function ($q) {
            $q->where('is_active', true);
        })->get();

        // 2. Ambil Data Sidang TA
        $sidangs = Sidang::with([
            'tugasAkhir.mahasiswa',
            'tugasAkhir.dosenPembimbing1',
            'tugasAkhir.dosenPembimbing2',
            'dosenPengujiKetua',
            'dosenPengujiSekretaris',
            'eventSidang.periode'
        ])->whereHas('eventSidang.periode', function ($q) {
            $q->where('is_active', true);
        })->get();

        // 3. Gabungkan Data
        $mergedJadwal = collect();

        foreach ($lstas as $lsta) {
            $mergedJadwal->push([
                // LSTA biasanya tidak dihapus lewat sini, jadi ID opsional/null
                'id' => $lsta->id,
                'tipe' => 'LSTA',
                'tanggal_raw' => $lsta->jadwal,
                'tanggal' => \Carbon\Carbon::parse($lsta->jadwal)->format('d M Y'),
                'jam' => \Carbon\Carbon::parse($lsta->jadwal)->format('H:i'),
                'ruangan' => $lsta->ruangan,
                'mahasiswa' => $lsta->tugasAkhir->mahasiswa->nama_lengkap . ' (' . $lsta->tugasAkhir->mahasiswa->nrp . ')',
                'judul' => $lsta->tugasAkhir->judul,
                'pembimbing' => '<small>1.</small> ' . $lsta->tugasAkhir->dosenPembimbing1->nama_lengkap . '<br>' .
                    '<small>2.</small> ' . $lsta->tugasAkhir->dosenPembimbing2->nama_lengkap,
                'penguji' => $lsta->dosenPenguji->nama_lengkap,
                'status' => $lsta->status,
            ]);
        }

        foreach ($sidangs as $sidang) {
            $mergedJadwal->push([
                // --- [PENTING] ID INI DIPERLUKAN UNTUK TOMBOL HAPUS ---
                'id' => $sidang->id,
                // ------------------------------------------------------
                'tipe' => 'SIDANG TA',
                'tanggal_raw' => $sidang->jadwal,
                'tanggal' => \Carbon\Carbon::parse($sidang->jadwal)->format('d M Y'),
                'jam' => \Carbon\Carbon::parse($sidang->jadwal)->format('H:i'),
                'ruangan' => $sidang->ruangan,
                'mahasiswa' => $sidang->tugasAkhir->mahasiswa->nama_lengkap . ' (' . $sidang->tugasAkhir->mahasiswa->nrp . ')',
                'judul' => $sidang->tugasAkhir->judul,
                'pembimbing' => '<small>1.</small> ' . $sidang->tugasAkhir->dosenPembimbing1->nama_lengkap . '<br>' .
                    '<small>2.</small> ' . $sidang->tugasAkhir->dosenPembimbing2->nama_lengkap,
                'penguji' => 'Ketua: ' . $sidang->dosenPengujiKetua->nama_lengkap . '<br>Sek: ' . $sidang->dosenPengujiSekretaris->nama_lengkap,
                'status' => $sidang->status,
            ]);
        }

        $finalData = $mergedJadwal->sortBy('tanggal_raw');

        return view('staff.jadwal-monitoring', [
            'jadwals' => $finalData
        ]);
    }

    /**
     * HAPUS JADWAL (Untuk kasus mahasiswa mundur/salah jadwal)
     */
    public function destroy($id)
    {
        // 1. Cari Data Sidang
        $sidang = Sidang::findOrFail($id);

        // 2. Cek Validasi: Jangan hapus yang sudah dinilai/lulus
        if (in_array($sidang->status, ['LULUS', 'TIDAK_LULUS', 'LULUS_REVISI'])) {
            return back()->with('error', 'BAHAYA: Sidang yang sudah dinilai tidak boleh dihapus!');
        }

        // 3. Hapus Data Relasi (Bersihkan sampah)
        try {
            // Hapus penilaian dosen jika ada (tapi belum final)
            $sidang->lembarPenilaians()->delete();

            // Hapus Sidang utama
            $sidang->delete();

            return back()->with('success', 'Jadwal berhasil dihapus. Mahasiswa telah dikeluarkan dari slot ini.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }
    public function destroyLsta($id)
    {
        // 1. Cari Data LSTA
        $lsta = Lsta::findOrFail($id);

        // 2. Cek Validasi: Jangan hapus jika sudah SELESAI
        if ($lsta->status == 'SELESAI') {
            return back()->with('error', 'LSTA yang sudah selesai tidak boleh dihapus.');
        }

        try {
            // 3. Hapus LSTA
            // Note: Data Tugas Akhir mahasiswa TIDAK terhapus, hanya jadwal LSTA-nya saja.
            $lsta->delete();

            return back()->with('success', 'Jadwal LSTA berhasil dihapus.');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus LSTA: ' . $e->getMessage());
        }
    }
}