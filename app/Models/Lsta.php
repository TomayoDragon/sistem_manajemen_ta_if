<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lsta extends Model {
    use HasFactory;
    protected $fillable = [
        'tugas_akhir_id',
        'pengajuan_sidang_id',
        'event_sidang_id',
        'dosen_penguji_id',
        'jadwal',
        'ruangan',
        'status',
    ];
    public function tugasAkhir() {
        return $this->belongsTo(TugasAkhir::class, 'tugas_akhir_id');
    }
    public function pengajuanSidang() {
        return $this->belongsTo(PengajuanSidang::class, 'pengajuan_sidang_id');
    }
    public function dosenPenguji() {
        return $this->belongsTo(Dosen::class, 'dosen_penguji_id');
    }

   public function eventSidang()
    {
        return $this->belongsTo(EventSidang::class, 'event_sidang_id');
    }
    public function lembarPenilaians()
    {
        return $this->morphMany(LembarPenilaian::class, 'penilaian');
    }
}