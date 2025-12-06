<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\SignatureService; 

class Mahasiswa extends Model
{
    use HasFactory;
    
    protected $table = 'mahasiswas';

    protected $fillable = [
        'nrp',
        'nama_lengkap',
    ];
    
    protected static function booted(): void
    {
        static::created(function (Mahasiswa $mahasiswa) {
            $signatureService = app(SignatureService::class);
            $signatureService->generateAndStoreKeys($mahasiswa);
        });
    }

    public function user()
    {
        return $this->hasOne(User::class, 'mahasiswa_id');
    }

    public function tugasAkhirs()
    {
        return $this->hasMany(TugasAkhir::class, 'mahasiswa_id');
    }
}