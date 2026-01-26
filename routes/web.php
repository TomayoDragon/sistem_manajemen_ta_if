<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// --- KUMPULAN SEMUA CONTROLLER KITA ---

// Global (Semua Role)
use App\Http\Controllers\IntegritasController;
use App\Http\Controllers\DokumenController;

// Mahasiswa
use App\Http\Controllers\Mahasiswa\DashboardController;
use App\Http\Controllers\Mahasiswa\UploadController;
use App\Http\Controllers\Mahasiswa\SidangController;
use App\Http\Controllers\Mahasiswa\BeritaAcaraController;
use App\Http\Controllers\Mahasiswa\DigitalSignatureController;
use App\Http\Controllers\Mahasiswa\RevisiController;

// Dosen
use App\Http\Controllers\Dosen\DashboardController as DosenDashboardController;
use App\Http\Controllers\Dosen\PenilaianController;
use App\Http\Controllers\Dosen\BimbinganController;

// Staff
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\ValidasiController;
use App\Http\Controllers\Staff\ArsipController;
use App\Http\Controllers\Staff\PeriodeController;
use App\Http\Controllers\Staff\JadwalExcelController;
use App\Http\Controllers\Staff\JadwalMonitoringController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rute utama ('/') mengarahkan ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});


// Grup utama untuk SEMUA user yang sudah login
Route::middleware(['auth'])->group(function () {

    // --- GRUP MAHASISWA ---
    Route::middleware(['role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::put('/update-no-hp', [DashboardController::class, 'updateNoHP'])->name('updateNoHP');
       // Route::delete('/error', [DashboardController::class, 'error'])->name('error');
        Route::get('/upload', [UploadController::class, 'create'])->name('upload');
        Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
        Route::get('/sidang', [SidangController::class, 'index'])->name('sidang');
        Route::get('/signature', [DigitalSignatureController::class, 'index'])->name('signature');
        Route::get('/sidang/{id}/revisi', [RevisiController::class, 'halaman_revisi'])->name('halaman_revisi');
        Route::put('/sidang/{id}/revisi', [RevisiController::class, 'update'])->name('update');
    });

    // --- GRUP DOSEN ---
    Route::middleware(['role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
        Route::get('/dashboard', [DosenDashboardController::class, 'index'])->name('dashboard');
        Route::get('/penilaian/{type}/{id}', [PenilaianController::class, 'show'])->name('penilaian.show');
        Route::post('/penilaian/{type}/{id}', [PenilaianController::class, 'store'])->name('penilaian.store');
        Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
        Route::post('/bimbingan/{tugasAkhir}/approve', [BimbinganController::class, 'approve'])->name('bimbingan.approve');
    });

    // --- GRUP STAFF ---
    Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('/ta/import', [App\Http\Controllers\Staff\TugasAkhirExcelController::class, 'index'])->name('import_ta');
        Route::get('/ta/template', [App\Http\Controllers\Staff\TugasAkhirExcelController::class, 'downloadTemplate'])->name('ta.template');
        Route::post('/ta/import', [App\Http\Controllers\Staff\TugasAkhirExcelController::class, 'import'])->name('ta.import.process');
        // 1. Dashboard
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

        // 2. Manajemen Arsip
        Route::get('/arsip', [ArsipController::class, 'index'])->name('arsip.index');
        Route::get('/arsip/{tugasAkhir}/detail', [ArsipController::class, 'show'])->name('arsip.show');

        // 3. Validasi Berkas
        Route::get('/validasi/{id}/review', [ValidasiController::class, 'show'])->name('validasi.review');
        Route::post('/validasi/{id}/process', [ValidasiController::class, 'process'])->name('validasi.process');

        // 4. Periode Sidang
        Route::get('/periode/create', [PeriodeController::class, 'create'])->name('periode.create');
        Route::post('/periode/akademik', [PeriodeController::class, 'storeAkademik'])->name('periode.store');
        Route::post('/periode/sidang', [PeriodeController::class, 'storeSidang'])->name('periode.storeSidang');
        Route::delete('/periode/{type}/{id}', [PeriodeController::class, 'destroy'])->name('periode.destroy');
        Route::patch('/periode/{type}/{id}/activate', [PeriodeController::class, 'activate'])->name('periode.activate');

        // 5. MANAJEMEN JADWAL (Excel & Atur Jadwal)
        Route::get('/jadwal/atur', [JadwalExcelController::class, 'index'])->name('jadwal.atur');

        // Proses Export & Import
        Route::get('/jadwal/export', [JadwalExcelController::class, 'exportTemplate'])->name('jadwal.export');
        Route::get('/jadwal/import', [JadwalExcelController::class, 'showImportForm'])->name('jadwal.import.form');
        Route::post('/jadwal/import', [JadwalExcelController::class, 'processImport'])->name('jadwal.import.process');

        // 6. MONITORING JADWAL & DELETE
        Route::get('/jadwal/monitoring', [JadwalMonitoringController::class, 'index'])->name('jadwal.monitoring');
        Route::delete('/jadwal/monitoring/sidang/{id}', [JadwalMonitoringController::class, 'destroy'])->name('sidang.destroy');
        Route::delete('/jadwal/monitoring/lsta/{id}', [JadwalMonitoringController::class, 'destroyLsta'])->name('lsta.destroy');
    });

    // --- GRUP ADMIN ---
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return 'Ini adalah Dashboard Admin.';
        })->name('dashboard');
    });

    // --- RUTE GLOBAL (UNTUK SEMUA ROLE) ---
    Route::get('/integritas/{dokumen}', [IntegritasController::class, 'show'])->name('integritas.show');
    Route::post('/integritas/{dokumen}', [IntegritasController::class, 'verify'])->name('integritas.verify');
    Route::get('/dokumen/{dokumen}/download', [DokumenController::class, 'download'])->name('dokumen.download');

    // Download Hasil Sidang (Berita Acara)
    Route::get('/hasil-sidang/{sidang}/{jenis}', [DokumenController::class, 'downloadHasilSidang'])
        ->name('dokumen.hasil-sidang');

    // === SOLUSI ERROR ROUTE NOT FOUND & DOWNLOAD ===
    // Ditaruh di sini agar namanya persis 'dokumen.sidang.download' 
    Route::get('/dokumen/sidang/{id}/revisi', [SidangController::class, 'downloadRevisi'])
        ->name('dokumen.sidang.download');
    // ===============================================

    // Rute Profil Bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});


// Memuat rute otentikasi
require __DIR__ . '/auth.php';