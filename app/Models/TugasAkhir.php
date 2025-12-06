<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasAkhir extends Model
{
    use HasFactory;

    protected $table = 'tugas_akhirs';

    protected $fillable = [
        'mahasiswa_id',
        'periode_id', // <-- TAMBAHKAN INI
        'judul',
        'dosen_pembimbing_1_id',
        'dosen_pembimbing_2_id',
        'status',
        'dosbing_1_approved_at', // <-- Ini dari logic Persetujuan Dosbing
        'dosbing_2_approved_at', // <-- Ini dari logic Persetujuan Dosbing
    ];
    /**
     * Relasi ke Dosen Pembimbing 1
     */
    public function dosenPembimbing1()
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_1_id');
    }

    /**
     * Relasi ke Dosen Pembimbing 2
     */
    public function dosenPembimbing2()
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_2_id');
    }

    /**
     * Relasi ke semua berkas terkait TA ini
     */
    /**
     * Mendapatkan semua paket pengajuan yang terkait dengan TA ini.
     * (Relasi One-to-Many)
     */
    public function pengajuanSidangs()
    {
        return $this->hasMany(PengajuanSidang::class, 'tugas_akhir_id');
    }

    // --- INI FUNGSI YANG HILANG ---
    /**
     * Relasi ke mahasiswa (TugasAkhir ini milik siapa)
     */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
    // --- AKHIR FUNGSI YANG HILANG ---
    public function lstas() // <-- Ganti nama jadi plural
    {
        return $this->hasMany(Lsta::class, 'tugas_akhir_id');
    }

    /**
     * Mendapatkan SEMUA jadwal Sidang yang terkait dengan TA ini.
     */
    public function sidangs() // <-- Ganti nama jadi plural
    {
        return $this->hasMany(Sidang::class, 'tugas_akhir_id');
    }
    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }
}