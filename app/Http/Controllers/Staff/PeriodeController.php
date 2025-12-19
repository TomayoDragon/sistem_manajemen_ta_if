<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Periode;
use App\Models\EventSidang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PeriodeController extends Controller
{
    public function create()
    {
        // 1. Ambil Data Periode Akademik
        $periodes = Periode::orderBy('tanggal_mulai', 'desc')->get();

        // 2. Ambil Data Periode Sidang (Event)
        $events = EventSidang::with('periode')
                    ->orderBy('tanggal_mulai', 'desc')
                    ->get();
        
        return view('staff.periode-create', compact('periodes', 'events'));
    }

    // --- LOGIC PERIODE AKADEMIK ---
    public function storeAkademik(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($request) {
            // Jika set aktif, matikan yang lain
            if ($request->has('is_active')) {
                Periode::query()->update(['is_active' => false]);
            }

            Periode::create([
                'nama' => $request->nama,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'is_active' => $request->has('is_active'),
            ]);
        });

        return back()->with('success', 'Periode Akademik berhasil ditambahkan.');
    }

    // --- LOGIC PERIODE SIDANG ---
    public function storeSidang(Request $request)
    {
        $request->validate([
            'periode_id' => 'required|exists:periodes,id',
            'nama_event' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'is_published' => 'nullable|boolean', // is_published = is_active
        ]);

        // Cek apakah tanggal event berada dalam range Periode Akademik (Opsional, tapi bagus)
        $periodeInduk = Periode::find($request->periode_id);
        if ($request->tanggal_mulai < $periodeInduk->tanggal_mulai || $request->tanggal_selesai > $periodeInduk->tanggal_selesai) {
             return back()->with('error', 'Tanggal Sidang harus berada di dalam rentang Tanggal Periode Akademik (' . $periodeInduk->nama . ').');
        }

        EventSidang::create([
            'periode_id' => $request->periode_id,
            'nama_event' => $request->nama_event,
            'tipe' => 'SIDANG_TA', // Default tipe
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_published' => $request->has('is_published'),
        ]);

        return back()->with('success', 'Periode Sidang berhasil ditambahkan.');
    }

    // --- LOGIC MENGAKTIFKAN PERIODE (Check Box / Button) ---
    public function activate($type, $id)
    {
        $now = Carbon::now()->format('Y-m-d');

        if ($type == 'akademik') {
            $item = Periode::findOrFail($id);
            
            // Validasi Tanggal
            if ($item->tanggal_selesai < $now) {
                return back()->with('error', 'Gagal! Periode ini sudah berakhir dan tidak bisa diaktifkan kembali.');
            }

            // Matikan semua, nyalakan ini
            Periode::query()->update(['is_active' => false]);
            $item->update(['is_active' => true]);

        } elseif ($type == 'sidang') {
            $item = EventSidang::findOrFail($id);

            // Validasi Tanggal
            if ($item->tanggal_selesai < $now) {
                return back()->with('error', 'Gagal! Periode Sidang ini sudah berakhir.');
            }

            // Toggle (Sidang mungkin bisa aktif bersamaan, atau mau single active juga?)
            // Disini saya buat toggle on/off biasa.
            $newState = !$item->is_published;
            $item->update(['is_published' => $newState]);
        }

        return back()->with('success', 'Status periode berhasil diperbarui.');
    }

    // --- LOGIC HAPUS ---
    public function destroy($type, $id)
    {
        $now = Carbon::now()->format('Y-m-d');

        if ($type == 'akademik') {
            $item = Periode::findOrFail($id);
            $relationCheck = $item->tugasAkhirs()->exists();
        } else {
            $item = EventSidang::findOrFail($id);
            $relationCheck = $item->pengajuanSidangs()->exists();
        }

        // 1. Cek apakah sedang berjalan/lewat
        if ($item->tanggal_mulai <= $now) {
            return back()->with('error', 'Tidak bisa menghapus periode yang sedang berjalan atau sudah lewat.');
        }

        // 2. Cek Relasi Data
        if ($relationCheck) {
            return back()->with('error', 'Gagal! Data sudah digunakan oleh Mahasiswa.');
        }

        $item->delete();
        return back()->with('success', 'Periode berhasil dihapus.');
    }
}