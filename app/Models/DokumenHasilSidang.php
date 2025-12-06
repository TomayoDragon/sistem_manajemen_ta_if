<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenHasilSidang extends Model
{
    use HasFactory;

    protected $fillable = [
        'sidang_id',
        'jenis_dokumen',
        'path_file',
        'nama_file_asli',
        'hash_sha512_full',
        'hash_blake2b_full',
        'hash_combined',
        'signature_data',
    ];

    public function sidang()
    {
        return $this->belongsTo(Sidang::class);
    }
}