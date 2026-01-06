<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailRevisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'lembar_penilaian_id',
        'isi_revisi',
        'keterangan_mahasiswa'
    ];
    
    // Relasi balik ke Parent (Opsional tapi berguna)
    public function lembarPenilaian()
    {
        return $this->belongsTo(LembarPenilaian::class, 'lembar_penilaian_id');
    }
}