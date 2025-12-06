<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BerkasTa extends Model
{
    use HasFactory;

    /**
     * Nama tabel (karena Laravel akan menebak 'berkas_tas')
     */
    protected $table = 'berkas_tas';

    /**
     * Kolom yang boleh diisi (untuk keamanan)
     */
    protected $fillable = [
        'tugas_akhir_id',
        'nama_file_asli',
        'path_penyimpanan',
        'tipe_berkas',
        'status_validasi',
        'catatan_validasi',
        'validator_id',
        'validated_at',
    ];

    /**
     * Berkas ini milik Tugas Akhir mana.
     */
    public function tugasAkhir()
    {
        return $this->belongsTo(TugasAkhir::class, 'tugas_akhir_id');
    }

    /**
     * Staf yang memvalidasi berkas ini.
     */
    public function validator()
    {
        return $this->belongsTo(Staff::class, 'validator_id');
    }
}