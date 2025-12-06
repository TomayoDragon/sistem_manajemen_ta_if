<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- TAMBAHKAN INI
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use HasFactory; // <-- DAN TAMBAHKAN INI
    /**
     * Daftar TA yang dibimbing sebagai Pembimbing 1
     */
    public function tugasAkhirBimbinganSatu()
    {
        return $this->hasMany(TugasAkhir::class, 'dosen_pembimbing_1_id');
    }

    /**
     * Daftar TA yang dibimbing sebagai Pembimbing 2
     */
    public function tugasAkhirBimbinganDua()
    {
        return $this->hasMany(TugasAkhir::class, 'dosen_pembimbing_2_id');
    }

    public function lembarPenilaians()
    {
        return $this->hasMany(LembarPenilaian::class, 'dosen_id');
    }
}