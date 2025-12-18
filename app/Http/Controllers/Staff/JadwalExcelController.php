<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Import OpenSpout
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;

// Import Models
use App\Models\Sidang;
use App\Models\Lsta;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\PengajuanSidang;
use App\Models\TugasAkhir;
use App\Models\EventSidang;
use App\Models\Periode;

class JadwalExcelController extends Controller
{
    public function showImportForm()
    {
        return view('staff.jadwal-import');
    }

    public function exportTemplate()
    {
        // 1. Ambil Pengajuan yang TERIMA tapi BELUM Punya Jadwal
        $acceptedPengajuans = PengajuanSidang::where('status_validasi', 'TERIMA')
            ->whereNull('event_sidang_id')
            ->with('tugasAkhir.mahasiswa', 'tugasAkhir.dosenPembimbing1', 'tugasAkhir.dosenPembimbing2')
            ->get();

        // 2. Persiapan Data Penunjang (Dosen & Ruangan Dummy)
        $allDosen = Dosen::all();
        $dummyRooms = ['TC.2.1', 'TC.2.2', 'R. Sidang 1', 'Lab Cyber'];

        // Waktu mulai: Minggu depan jam 08:00
        $startDate = Carbon::now()->addDays(7)->setTime(8, 0);
        $counter = 0;

        $writer = new Writer();
        $filePath = tempnam(sys_get_temp_dir(), 'jadwal_draft_');
        $writer->openToFile($filePath);

        // 3. Header Excel
        $headerStyle = (new Style())->setFontBold()->setFontColor(Color::WHITE)->setBackgroundColor(Color::rgb(10, 46, 108));
        $headerCells = [
            Cell::fromValue('nrp'),
            Cell::fromValue('nama_mahasiswa'),
            Cell::fromValue('dosbing1'),
            Cell::fromValue('dosbing2'),
            Cell::fromValue('tanggal'),
            Cell::fromValue('jam'),
            Cell::fromValue('ruang'),
            Cell::fromValue('ketua'),
            Cell::fromValue('sekretaris'),
        ];
        $writer->addRow(new Row($headerCells, $headerStyle));

        // 4. Loop & Isi Data Otomatis (AUTO-FILL LOGIC)
        foreach ($acceptedPengajuans as $pengajuan) {
            // A. Tentukan Waktu (Sequential per 2 jam)
            // Setiap 4 mahasiswa, ganti hari (asumsi 1 hari max 4 sidang per ruangan)
            $dayOffset = floor($counter / 4);
            $timeOffset = $counter % 4;

            $jadwalDraft = $startDate->copy()->addDays($dayOffset)->addHours($timeOffset * 2); // Jeda 2 jam

            // Hindari hari Sabtu/Minggu (Simple Logic)
            if ($jadwalDraft->isWeekend()) {
                $jadwalDraft->addDays(2);
            }

            // B. Tentukan Penguji (Random tapi Valid)
            $pembimbingIds = [
                $pengajuan->tugasAkhir->dosen_pembimbing_1_id,
                $pengajuan->tugasAkhir->dosen_pembimbing_2_id
            ];

            // Ambil dosen yang BUKAN pembimbing
            $availablePenguji = $allDosen->whereNotIn('id', $pembimbingIds);

            // Pilih 2 penguji acak
            if ($availablePenguji->count() >= 2) {
                $randomPenguji = $availablePenguji->random(2);
                $ketua = $randomPenguji[0];
                $sekretaris = $randomPenguji[1];
            } else {
                // Fallback jika data dosen kurang
                $ketua = $allDosen->first();
                $sekretaris = $allDosen->last();
            }

            // C. Tulis Baris Excel
            $dataCells = [
                Cell::fromValue($pengajuan->tugasAkhir->mahasiswa->nrp),
                Cell::fromValue($pengajuan->tugasAkhir->mahasiswa->nama_lengkap),
                Cell::fromValue($pengajuan->tugasAkhir->dosenPembimbing1->nama_lengkap),
                Cell::fromValue($pengajuan->tugasAkhir->dosenPembimbing2->nama_lengkap),

                // Data Suggestion (Bisa diedit Staf nanti)
                Cell::fromValue($jadwalDraft->format('d/m/Y')),
                Cell::fromValue($jadwalDraft->format('H:i')),
                Cell::fromValue($dummyRooms[array_rand($dummyRooms)]), // Ruang Acak
                Cell::fromValue($ketua->nama_lengkap),
                Cell::fromValue($sekretaris->nama_lengkap),
            ];
            $writer->addRow(new Row($dataCells, null));

            $counter++;
        }

        $writer->close();
        return response()->download($filePath, 'DRAF_JADWAL_AUTO.xlsx')->deleteFileAfterSend(true);
    }
    /**
     * Memproses file Excel (REVISI: MENDUKUNG OVERWRITE).
     */
    public function processImport(Request $request)
    {
        $request->validate(['file_jadwal' => 'required|file|mimes:xlsx|max:2048']);
        $filePath = $request->file('file_jadwal')->getPathname();

        DB::beginTransaction();
        try {
            $reader = new Reader();
            $reader->open($filePath);

            $header = [];
            $isHeader = true;
            $errors = [];
            $rowNumber = 1;

            // Cari Event Sidang Aktif
            $activeEvent = EventSidang::whereHas('periode', function ($q) {
                $q->where('is_active', true);
            })->where('tipe', 'SIDANG_TA')->first();

            if (!$activeEvent) {
                throw new \Exception("Tidak ada Event Sidang aktif. Harap buat event baru di menu Periode.");
            }

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($isHeader) {
                        $cells = $row->getCells();
                        foreach ($cells as $cell) {
                            $header[] = strtolower($cell->getValue());
                        }
                        $isHeader = false;
                        continue;
                    }

                    $data = $this->mapRowData($row->getCells(), $header);
                    $rowNumber++;

                    $nrp = $data['nrp'] ?? null;
                    if (!$nrp)
                        continue;

                    $tanggal = $data['tanggal'] ?? null;
                    $jam = $data['jam'] ?? null;
                    $ruang = $data['ruang'] ?? null;
                    $namaKetua = $data['ketua'] ?? null;
                    $namaSekretaris = $data['sekretaris'] ?? null;

                    if (empty($tanggal) || empty($jam) || empty($ruang) || empty($namaKetua) || empty($namaSekretaris)) {
                        $errors[] = "Baris $rowNumber (NRP: $nrp): Data jadwal tidak lengkap.";
                        continue;
                    }

                    $mahasiswa = Mahasiswa::where('nrp', $nrp)->first();
                    if (!$mahasiswa) {
                        $errors[] = "Baris $rowNumber: NRP $nrp tidak ditemukan.";
                        continue;
                    }

                    $tugasAkhir = $mahasiswa->tugasAkhirs()->latest()->first();
                    if (!$tugasAkhir) {
                        $errors[] = "Baris $rowNumber: Mahasiswa $nrp tidak punya TA.";
                        continue;
                    }

                    // --- PERBAIKAN UTAMA DI SINI ---
                    // Kita mencari pengajuan TERIMA, TAPI KITA HAPUS SYARAT 'whereNull'
                    // Agar kita bisa menemukan (dan mengupdate) pengajuan yang sudah ada jadwalnya.
                    $pengajuan = $tugasAkhir->pengajuanSidangs()
                        ->where('status_validasi', 'TERIMA')
                        ->latest()
                        ->first();

                    if (!$pengajuan) {
                        $errors[] = "Baris $rowNumber: Berkas $nrp belum divalidasi 'TERIMA'.";
                        continue;
                    }
                    // -------------------------------

                    $ketua = Dosen::where('nama_lengkap', 'LIKE', $namaKetua . '%')->first();
                    $sekretaris = Dosen::where('nama_lengkap', 'LIKE', $namaSekretaris . '%')->first();
                    if (!$ketua || !$sekretaris) {
                        $errors[] = "Baris $rowNumber: Dosen Penguji tidak ditemukan ($namaKetua / $namaSekretaris).";
                        continue;
                    }

                    try {
                        $parsedTanggal = ($tanggal instanceof \DateTime) ? Carbon::instance($tanggal) : Carbon::createFromFormat('d/m/Y', $tanggal);
                        $startTime = explode('-', $jam)[0];
                        $parsedJadwal = $parsedTanggal->setTimeFromTimeString($startTime);
                    } catch (\Exception $e) {
                        $errors[] = "Baris $rowNumber: Format tanggal/jam salah.";
                        continue;
                    }

                    // --- UPDATE OR CREATE JADWAL ---
                    // Karena kita pakai 'updateOrCreate', jika data sudah ada, akan ditimpa (Revisi)

                    // 1. Update/Create LSTA
                    Lsta::updateOrCreate(
                        [
                            'tugas_akhir_id' => $tugasAkhir->id,
                            'pengajuan_sidang_id' => $pengajuan->id
                        ],
                        [
                            'event_sidang_id' => $activeEvent->id,
                            'dosen_penguji_id' => $ketua->id,
                            'jadwal' => $parsedJadwal,
                            'ruangan' => $ruang,
                            'status' => 'TERJADWAL'
                        ]
                    );

                    // 2. Update/Create Sidang
                    Sidang::updateOrCreate(
                        [
                            'tugas_akhir_id' => $tugasAkhir->id,
                            'pengajuan_sidang_id' => $pengajuan->id
                        ],
                        [
                            'event_sidang_id' => $activeEvent->id,
                            'dosen_penguji_ketua_id' => $ketua->id,
                            'dosen_penguji_sekretaris_id' => $sekretaris->id,
                            'jadwal' => $parsedJadwal,
                            'ruangan' => $ruang,
                            'status' => 'TERJADWAL'
                        ]
                    );

                    // 3. Pastikan pengajuan terkunci ke event ini
                    $pengajuan->event_sidang_id = $activeEvent->id;
                    $pengajuan->save();
                }
            }
            $reader->close();

            if (!empty($errors)) {
                DB::rollBack();
                return redirect()->route('staff.jadwal.import.form')->with('import_errors', $errors);
            }

            DB::commit();
            return redirect()->route('staff.dashboard')->with('success', 'Jadwal berhasil diperbarui (Revisi Tersimpan).');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('staff.jadwal.import.form')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    private function mapRowData(array $cells, array $header): array
    {
        $data = [];
        foreach ($cells as $index => $cell) {
            if (isset($header[$index])) {
                $data[$header[$index]] = $cell->getValue();
            }
        }
        return $data;
    }
}