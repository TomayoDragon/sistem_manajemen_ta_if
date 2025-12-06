<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model {
    use HasFactory;
    protected $fillable = [
        'sidang_id',
        'jumlah_nilai_mentah_nma',
        'rata_rata_nma',
        'nilai_relatif_nr',
        'hasil_ujian',
        'path_file_generated', // Kita tambahkan juga untuk nanti
    ];
    public function sidang() {
        return $this->belongsTo(Sidang::class, 'sidang_id'); // BA milik 1 Sidang
    }
}