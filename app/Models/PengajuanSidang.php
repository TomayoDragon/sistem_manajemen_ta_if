<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanSidang extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_sidangs';

    /**
     * Kolom yang boleh diisi (mass assignable)
     * (Ini adalah versi yang benar setelah kita hapus path file)
     */
    protected $fillable = [
        'tugas_akhir_id',
        'event_sidang_id', // <-- TAMBAHKAN INI
        'status_validasi',
        'catatan_validasi',
        'validator_id',
        'validated_at',
    ];

    // --- INI ADALAH FUNGSI YANG HILANG ---
    /**
     * Relasi: Pengajuan ini milik TA mana.
     */
    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class, 'tugas_akhir_id');
    }
    // --- AKHIR FUNGSI YANG HILANG ---

    /**
     * Relasi: Siapa staf yang memvalidasi.
     */
    public function validator()
    {
        return $this->belongsTo(Staff::class, 'validator_id');
    }

    /**
     * Relasi: LSTA yang dibuat dari pengajuan ini
     */
    public function lstas()
    {
        return $this->hasMany(Lsta::class, 'pengajuan_sidang_id');
    }

    /**
     * Relasi: Sidang yang dibuat dari pengajuan ini
     */
    public function sidangs()
    {
        return $this->hasMany(Sidang::class, 'pengajuan_sidang_id');
    }

    /**
     * Relasi: Satu paket pengajuan memiliki BANYAK dokumen.
     */
    public function dokumen()
    {
        return $this->hasMany(DokumenPengajuan::class, 'pengajuan_sidang_id');
    }
    public function eventSidang()
    {
        return $this->belongsTo(EventSidang::class, 'event_sidang_id');
    }
}