<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// --- IMPORT DARI LIBRARY BARU KITA (OPEN SPOUT) ---
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;

// --- Model Database Kita ---
use App\Models\Sidang;
use App\Models\Lsta;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\PengajuanSidang;
use App\Models\TugasAkhir;
use App\Models\EventSidang; // <-- IMPORT MODEL BARU
use App\Models\Periode; // <-- IMPORT MODEL BARU

class JadwalExcelController extends Controller
{
    /**
     * Menampilkan halaman form upload Excel.
     */
    public function showImportForm()
    {
        return view('staff.jadwal-import');
    }

    /**
     * FITUR BARU: Export Draf Jadwal (Auto-Generate)
     * Persis seperti yang Anda minta (Point 3)
     */
    public function exportTemplate()
    {
        // 1. Ambil semua pengajuan 'TERIMA' yang belum dijadwalkan
        $acceptedPengajuans = PengajuanSidang::where('status_validasi', 'TERIMA')
                                ->whereNull('event_sidang_id') // <-- Kunci: Belum masuk gelombang
                                ->with('tugasAkhir.mahasiswa', 'tugasAkhir.dosenPembimbing1', 'tugasAkhir.dosenPembimbing2')
                                ->get();
        
        $allDosen = Dosen::all();
        $dummyRooms = ['TC.2.1', 'TC.2.2', 'Ruang Rapat IF', 'Lab Cyber'];
        $startDate = Carbon::now()->addDays(7)->setTime(9, 0);

        // 2. Buat file Excel baru menggunakan OpenSpout Writer
        $writer = new Writer();
        $filePath = tempnam(sys_get_temp_dir(), 'jadwal_draft_');
        $writer->openToFile($filePath);

        // 3. Buat Style untuk Header
        $headerStyle = (new Style())->setFontBold()->setFontColor(Color::WHITE)->setBackgroundColor(Color::rgb(10, 46, 108));
        
        $headerCells = [
            Cell::fromValue('nrp'), Cell::fromValue('nama_mahasiswa'),
            Cell::fromValue('dosbing1'), Cell::fromValue('dosbing2'),
            Cell::fromValue('tanggal'), Cell::fromValue('jam'),
            Cell::fromValue('ruang'), Cell::fromValue('ketua'),
            Cell::fromValue('sekretaris'),
        ];
        $writer->addRow(new Row($headerCells, $headerStyle));

        $counter = 0;
        // 4. Isi data mahasiswa dan data JADWAL DUMMY
        foreach ($acceptedPengajuans as $pengajuan) {
            
            $pembimbingIds = [$pengajuan->tugasAkhir->dosen_pembimbing_1_id, $pengajuan->tugasAkhir->dosen_pembimbing_2_id];
            $availablePenguji = $allDosen->whereNotIn('id', $pembimbingIds);
            
            $ketua = $availablePenguji->count() > 0 ? $availablePenguji->random() : null;
            $sekretaris = $availablePenguji->count() > 1 ? $availablePenguji->where('id', '!=', $ketua->id)->random() : null;
            
            $jadwal = $startDate->copy()->addHours($counter);
            $ruang = $dummyRooms[array_rand($dummyRooms)];

            $dataCells = [
                Cell::fromValue($pengajuan->tugasAkhir->mahasiswa->nrp),
                Cell::fromValue($pengajuan->tugasAkhir->mahasiswa->nama_lengkap),
                Cell::fromValue($pengajuan->tugasAkhir->dosenPembimbing1->nama_lengkap),
                Cell::fromValue($pengajuan->tugasAkhir->dosenPembimbing2->nama_lengkap),
                Cell::fromValue($jadwal->format('d/m/Y')),
                Cell::fromValue($jadwal->format('H:i') . '-' . $jadwal->copy()->addMinutes(90)->format('H:i')),
                Cell::fromValue($ruang),
                Cell::fromValue($ketua ? $ketua->nama_lengkap : ''),
                Cell::fromValue($sekretaris ? $sekretaris->nama_lengkap : ''),
            ];
            $writer->addRow(new Row($dataCells, null));
            $counter++;
        }

        $writer->close();

        // 5. Download file
        return response()->download($filePath, 'DRAF_JADWAL_SIDANG.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Memproses file Excel yang di-upload menggunakan OpenSpout.
     * (Sekarang menggunakan 'periodes' dan 'events_sidang')
     */
    public function processImport(Request $request)
    {
        $request->validate(['file_jadwal' => 'required|file|mimes:xlsx|max:2048']);
        $filePath = $request->file('file_jadwal')->getPathname();

        DB::beginTransaction();
        try {
            $reader = new Reader();
            $reader->open($filePath);

            $header = []; $isHeader = true; $errors = []; $rowNumber = 1;

            // --- LOGIKA BARU (POINT 4 ANDA) ---
            // Secara otomatis pilih event sidang/lsta terdekat yang belum diadakan
            // (Kita asumsikan event SIDANG TA yang aktif)
            $activeEvent = EventSidang::whereHas('periode', function($q) {
                                $q->where('is_active', true);
                            })
                            ->where('tipe', 'SIDANG_TA') 
                            ->first();

            if (!$activeEvent) {
                throw new \Exception("Tidak ada Periode/Event Sidang yang aktif. Harap buat event baru.");
            }
            // --- AKHIR LOGIKA BARU ---


            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    if ($isHeader) {
                        $cells = $row->getCells();
                        foreach ($cells as $cell) { $header[] = strtolower($cell->getValue()); }
                        $isHeader = false; continue;
                    }
                    $data = $this->mapRowData($row->getCells(), $header);
                    $rowNumber++;
                    
                    $nrp = $data['nrp'] ?? null;
                    if (!$nrp) continue; 

                    $tanggal = $data['tanggal'] ?? null;
                    $jam = $data['jam'] ?? null;
                    $ruang = $data['ruang'] ?? null;
                    $namaKetua = $data['ketua'] ?? null;
                    $namaSekretaris = $data['sekretaris'] ?? null;

                    if (empty($tanggal) || empty($jam) || empty($ruang) || empty($namaKetua) || empty($namaSekretaris)) {
                        $errors[] = "Baris $rowNumber (NRP: $nrp): Data tanggal, jam, ruang, atau penguji tidak boleh kosong.";
                        continue;
                    }

                    $mahasiswa = Mahasiswa::where('nrp', $nrp)->first();
                    if (!$mahasiswa) { $errors[] = "Baris $rowNumber: NRP $nrp tidak ditemukan."; continue; }
                    $tugasAkhir = $mahasiswa->tugasAkhirs()->latest()->first();
                    if (!$tugasAkhir) { $errors[] = "Baris $rowNumber: Mahasiswa $nrp tidak punya TA."; continue; }
                    
                    // Cek pengajuan 'TERIMA' yang BELUM punya event
                    $pengajuan = $tugasAkhir->pengajuanSidangs()
                                        ->where('status_validasi', 'TERIMA')
                                        ->whereNull('event_sidang_id') 
                                        ->latest()
                                        ->first();
                    if (!$pengajuan) { $errors[] = "Baris $rowNumber: Berkas $nrp belum divalidasi 'TERIMA' atau sudah terjadwal."; continue; }
                    
                    $ketua = Dosen::where('nama_lengkap', 'LIKE', $namaKetua . '%')->first();
                    $sekretaris = Dosen::where('nama_lengkap', 'LIKE', $namaSekretaris . '%')->first();
                    if (!$ketua || !$sekretaris) { $errors[] = "Baris $rowNumber: Nama Dosen Penguji ($namaKetua / $namaSekretaris) tidak ditemukan di database."; continue; }

                    try {
                        $parsedTanggal = ($tanggal instanceof \DateTime) ? Carbon::instance($tanggal) : Carbon::createFromFormat('d/m/Y', $tanggal);
                        $startTime = explode('-', $jam)[0];
                        $parsedJadwal = $parsedTanggal->setTimeFromTimeString($startTime);
                    } catch (\Exception $e) {
                        $errors[] = "Baris $rowNumber: Format tanggal/jam salah ($tanggal, $jam)."; continue;
                    }

                    // Buat Jadwal LSTA
                    Lsta::create([
                        'tugas_akhir_id' => $tugasAkhir->id,
                        'pengajuan_sidang_id' => $pengajuan->id,
                        'event_sidang_id' => $activeEvent->id, // <-- Tautkan ke Event
                        'dosen_penguji_id' => $ketua->id, 
                        'jadwal' => $parsedJadwal, 'ruangan' => $ruang, 'status' => 'TERJADWAL'
                    ]);
                    // Buat Jadwal Sidang
                    Sidang::create([
                        'tugas_akhir_id' => $tugasAkhir->id,
                        'pengajuan_sidang_id' => $pengajuan->id,
                        'event_sidang_id' => $activeEvent->id, // <-- Tautkan ke Event
                        'dosen_penguji_ketua_id' => $ketua->id, 
                        'dosen_penguji_sekretaris_id' => $sekretaris->id, 
                        'jadwal' => $parsedJadwal, 'ruangan' => $ruang, 'status' => 'TERJADWAL'
                    ]);

                    // Kunci mahasiswa ini ke event
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
            return redirect()->route('staff.dashboard')->with('success', 'File jadwal berhasil di-import dan diproses.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('staff.jadwal.import.form')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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