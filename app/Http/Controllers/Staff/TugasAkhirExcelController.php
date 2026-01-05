<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\TugasAkhir;
use App\Models\Dosen;
use App\Models\Periode;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;

class TugasAkhirExcelController extends Controller
{
    public function index()
    {
        return view('staff.import_ta');
    }

    public function downloadTemplate()
    {
        $writer = new Writer();
        $filePath = tempnam(sys_get_temp_dir(), 'template_ta_');
        $writer->openToFile($filePath);

        // Header Style
        $headerStyle = (new Style())->setFontBold()->setFontColor(Color::WHITE)->setBackgroundColor(Color::rgb(10, 46, 108));
        
        $writer->addRow(new Row([
            Cell::fromValue('nrp'),
            Cell::fromValue('nama_mahasiswa'),
            Cell::fromValue('judul_ta'),
            Cell::fromValue('npk_dosbing1'),
            Cell::fromValue('npk_dosbing2'),
        ], $headerStyle));

        // Dummy Data
        $writer->addRow(new Row([
            Cell::fromValue('160421001'),
            Cell::fromValue('Budi Santoso'),
            Cell::fromValue('Sistem Informasi Manajemen'),
            Cell::fromValue('199001'), // NPK Dosen
            Cell::fromValue(''),
        ]));

        $writer->close();
        return response()->download($filePath, 'TEMPLATE_IMPORT_TA.xlsx')->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate(['file_excel' => 'required|file|mimes:xlsx']);
        
        // 1. Cek Periode Aktif
        $activePeriode = Periode::where('is_active', true)->first();
        if (!$activePeriode) {
            return back()->with('import_errors', ['CRITICAL ERROR: Tidak ada Periode TA yang berstatus AKTIF.']);
        }

        DB::beginTransaction();
        try {
            $reader = new Reader();
            $reader->open($request->file('file_excel')->getPathname());
            
            $errors = [];
            $successCount = 0;
            $rowNum = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowNum++;
                    if ($rowNum == 1) continue; // Skip Header

                    $cells = $row->getCells();
                    
                    // Ambil value & Trim
                    $nrp   = isset($cells[0]) ? trim((string)$cells[0]->getValue()) : '';
                    $nama  = isset($cells[1]) ? trim((string)$cells[1]->getValue()) : '';
                    $judul = isset($cells[2]) ? trim((string)$cells[2]->getValue()) : '';
                    $npk1  = isset($cells[3]) ? trim((string)$cells[3]->getValue()) : '';
                    $npk2  = isset($cells[4]) ? trim((string)$cells[4]->getValue()) : '';

                    // Validasi Dasar
                    if (empty($nrp) || empty($nama) || empty($judul) || empty($npk1)) {
                        if (!empty($nrp) || !empty($nama)) {
                            $errors[] = "Baris $rowNum: Data tidak lengkap.";
                        }
                        continue; 
                    }

                    // 2. Cek Validitas Dosen
                    $dosbing1 = Dosen::where('npk', $npk1)->first();
                    if (!$dosbing1) {
                        $errors[] = "Baris $rowNum: Dosen NPK '$npk1' tidak ditemukan.";
                        continue;
                    }

                    $dosbing2Id = null;
                    if (!empty($npk2)) {
                        $dosbing2 = Dosen::where('npk', $npk2)->first();
                        if (!$dosbing2) {
                            $errors[] = "Baris $rowNum: Dosen 2 NPK '$npk2' tidak ditemukan.";
                            continue;
                        }
                        $dosbing2Id = $dosbing2->id;
                    }

                    // 3. Buat/Ambil Mahasiswa
                    $mahasiswa = Mahasiswa::firstOrCreate(
                        ['nrp' => $nrp],
                        ['nama_lengkap' => $nama]
                    );

                    // 4. Buat Akun User (PERBAIKAN DISINI)
                    if (!User::where('login_id', $nrp)->exists()) {
                        User::create([
                            'name' => $nama, // Pastikan kolom 'name' ada di tabel users Anda, jika error ganti jadi 'username' atau hapus
                            'login_id' => $nrp,
                            'email' => $nrp . '@student.ubaya.ac.id',
                            'password' => Hash::make('password123'),
                            'mahasiswa_id' => $mahasiswa->id, // <--- INI SUDAH CUKUP UNTUK MENENTUKAN ROLE
                        ]);
                        // HAPUS ->assignRole('mahasiswa') KARENA ANDA TIDAK PAKAI SPATIE
                    }

                    // 5. Cek Duplikasi Tugas Akhir
                    $exists = TugasAkhir::where('mahasiswa_id', $mahasiswa->id)
                                        ->where('periode_id', $activePeriode->id)
                                        ->exists();

                    if ($exists) {
                        $errors[] = "Baris $rowNum: Mahasiswa $nrp sudah terdaftar TA.";
                        continue;
                    }

                    // 6. Simpan Tugas Akhir
                    TugasAkhir::create([
                        'mahasiswa_id' => $mahasiswa->id,
                        'periode_id'   => $activePeriode->id,
                        'judul'        => $judul,
                        'dosen_pembimbing_1_id' => $dosbing1->id,
                        'dosen_pembimbing_2_id' => $dosbing2Id,
                        'status'       => 'Bimbingan', 
                    ]);

                    $successCount++;
                }
            }

            if ($successCount == 0 && count($errors) > 0) {
                DB::rollBack();
                return back()->with('import_errors', $errors);
            }

            DB::commit();

            if (count($errors) > 0) {
                return back()->with('success', "Import $successCount data berhasil, dengan beberapa error.")
                             ->with('import_errors', $errors);
            }

            return back()->with('success', "SUKSES! Berhasil menambahkan $successCount Tugas Akhir baru.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('import_errors', ['SYSTEM ERROR: ' . $e->getMessage()]);
        }
    }
}