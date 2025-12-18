<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Periode;
use Carbon\Carbon;

class PeriodeController extends Controller
{
    /**
     * Menampilkan form tambah periode DAN daftar periode.
     */
    public function create()
    {
        // Ambil semua periode, urutkan dari yang terbaru
        $periodes = Periode::orderBy('tanggal_mulai', 'desc')->get();
        
        return view('staff.periode-create', [
            'periodes' => $periodes
        ]);
    }

    /**
     * Menyimpan periode baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->has('is_active') && $request->is_active == 1) {
            Periode::query()->update(['is_active' => false]);
        }

        Periode::create([
            'nama' => $request->nama,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('periode.create') // Redirect ke halaman yang sama
            ->with('success', 'Periode baru berhasil ditambahkan.');
    }

    /**
     * Menghapus periode (Hanya jika belum dimulai).
     */
    public function destroy($id)
    {
        $periode = Periode::findOrFail($id);

        // LOGIKA PENGAMAN:
        // Jika tanggal mulai <= hari ini, berarti sedang berjalan atau sudah lewat.
        // Maka TIDAK BOLEH dihapus.
        if ($periode->tanggal_mulai <= now()->format('Y-m-d')) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus! Periode ini sedang berjalan atau sudah lewat.');
        }

        // Cek relasi (opsional tapi disarankan)
        // Jika sudah ada Tugas Akhir yang terhubung, tolak
        if ($periode->tugasAkhirs()->exists()) {
             return redirect()->back()
                ->with('error', 'Gagal menghapus! Sudah ada mahasiswa yang terdaftar di periode ini.');
        }

        $periode->delete();

        return redirect()->back()
            ->with('success', 'Periode masa depan berhasil dihapus.');
    }
}