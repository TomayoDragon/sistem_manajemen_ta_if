<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute; // <-- PASTIKAN INI DI-IMPORT

class DokumenPengajuan extends Model
{
    use HasFactory;
    
    protected $table = 'dokumen_pengajuans';

    protected $fillable = [
        'pengajuan_sidang_id',
        'tipe_dokumen',
        'path_penyimpanan',
        'nama_file_asli',
        'hash_sha512_full',
        'hash_blake2b_full',
        'hash_combined',
        'signature_data',
        'is_signed',
    ];

    /**
     * Relasi: Dokumen ini milik PengajuanSidang mana.
     */
    public function pengajuanSidang()
    {
        return $this->belongsTo(PengajuanSidang::class, 'pengajuan_sidang_id');
    }

    /**
     * Accessor otomatis untuk mengubah signature_data (biner)
     * menjadi string Base64 yang aman ditampilkan.
     *
     * Kita bisa memanggilnya di view dengan: $dokumen->signature_base64
     */
    protected function signatureBase64(): Attribute
    {
        return Attribute::make(
            // Cek jika $this->signature_data ada sebelum di-encode
            get: fn () => $this->signature_data ? base64_encode($this->signature_data) : null,
        );
    }
}