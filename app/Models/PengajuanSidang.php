<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanSidang extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_sidangs';

    protected $fillable = [
        'tugas_akhir_id',
        'event_sidang_id',
        'jenis_sidang', // <-- PASTIKAN KOLOM INI ADA (sesuai diskusi kita sebelumnya)
        'status_validasi',
        'catatan_validasi',
        'validator_id',
        'validated_at',
    ];

    /**
     * Relasi: Pengajuan ini milik TA mana.
     */
    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class, 'tugas_akhir_id');
    }

    /**
     * Relasi: Siapa staf yang memvalidasi.
     */
    public function validator()
    {
        return $this->belongsTo(Staff::class, 'validator_id');
    }

    /**
     * Relasi ke Event Sidang (Periode)
     */
    public function eventSidang()
    {
        return $this->belongsTo(EventSidang::class, 'event_sidang_id');
    }

    /**
     * Relasi: Dokumen syarat (Banyak dokumen untuk 1 pengajuan)
     */
    public function dokumen()
    {
        // Sesuaikan nama Model dokumen Anda, misalnya DokumenPengajuanSidang atau DokumenPengajuan
        return $this->hasMany(DokumenPengajuan::class, 'pengajuan_sidang_id');
    }

    // =================================================================
    // PERBAIKAN PENTING DI SINI (Gunakan Singlar 'sidang' & hasOne)
    // =================================================================

    /**
     * Relasi: Jadwal LSTA yang dihasilkan dari pengajuan ini
     * (Gunakan hasOne karena 1 pengajuan = 1 jadwal)
     */
    public function lsta()
    {
        return $this->hasOne(Lsta::class, 'pengajuan_sidang_id');
    }

    /**
     * Relasi: Jadwal Sidang yang dihasilkan dari pengajuan ini
     * (Nama fungsi harus 'sidang' agar cocok dengan controller whereDoesntHave('sidang'))
     */
    public function sidang()
    {
        return $this->hasOne(Sidang::class, 'pengajuan_sidang_id');
    }
}