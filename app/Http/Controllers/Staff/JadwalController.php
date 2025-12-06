<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSidang;
use App\Models\Dosen;
use App\Models\Lsta;
use App\Models\Sidang;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalController extends Controller
{
    /**
     * Meng-generate jadwal dummy untuk SEMUA pengajuan yang
     * statusnya 'TERIMA' tapi belum punya jadwal.
     */
    public function generateAll()
    {
        // 1. Ambil semua pengajuan yang 'TERIMA' & belum punya LSTA/Sidang
        $pengajuansToSchedule = PengajuanSidang::where('status_validasi', 'TERIMA')
                                  ->doesntHave('lstas') // Kunci: Hanya yg belum punya LSTA
                                  ->with('tugasAkhir')
                                  ->get();
        
        // 2. Ambil semua ID Dosen
        $allDosenIds = Dosen::pluck('id');

        // 3. Siapkan data dummy
        $dummyRooms = ['TC.2.1', 'TC.2.2', 'Ruang Rapat IF', 'Lab Cyber'];
        $startDate = Carbon::now()->addDays(7)->setTime(9, 0); 

        $counter = 0;

        // 4. Loop setiap pengajuan dan buat jadwal
        foreach ($pengajuansToSchedule as $pengajuan) {
            
            $pembimbingIds = [
                $pengajuan->tugasAkhir->dosen_pembimbing_1_id,
                $pengajuan->tugasAkhir->dosen_pembimbing_2_id
            ];
            
            $availablePenguji = $allDosenIds->diff($pembimbingIds);

            // --- BUAT JADWAL LSTA (DUMMY) ---
            if ($availablePenguji->count() > 0) {
                Lsta::create([
                    'tugas_akhir_id' => $pengajuan->tugas_akhir_id,
                    'pengajuan_sidang_id' => $pengajuan->id,
                    'dosen_penguji_id' => $availablePenguji->random(),
                    'jadwal' => $startDate->copy()->addHours($counter),
                    'ruangan' => $dummyRooms[array_rand($dummyRooms)],
                    'status' => 'TERJADWAL',
                ]);
            }

            // --- BUAT JADWAL SIDANG (DUMMY) ---
            if ($availablePenguji->count() > 1) {
                $pengujiSidang = $availablePenguji->random(2);
                Sidang::create([
                    'tugas_akhir_id' => $pengajuan->tugas_akhir_id,
                    'pengajuan_sidang_id' => $pengajuan->id,
                    'dosen_penguji_ketua_id' => $pengujiSidang[0],
                    'dosen_penguji_sekretaris_id' => $pengujiSidang[1],
                    'jadwal' => $startDate->copy()->addHours($counter)->addDays(3),
                    'ruangan' => $dummyRooms[array_rand($dummyRooms)],
                    'status' => 'TERJADWAL',
                ]);
            }

            $counter++;
        }

        // 5. Redirect kembali
        if ($counter == 0) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Tidak ada jadwal baru yang di-generate (semua sudah terjadwal).');
        }

        return redirect()->route('staff.dashboard')
            ->with('success', "Berhasil men-generate $counter jadwal LSTA & Sidang baru.");
    }
}