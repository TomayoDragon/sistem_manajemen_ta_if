<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSidang extends Model
{
    use HasFactory;
    protected $table = 'events_sidangs';
    protected $fillable = ['periode_id', 'nama_event', 'tipe', 'is_published'];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }
    public function pengajuanSidangs()
    {
        return $this->hasMany(PengajuanSidang::class, 'event_sidang_id');
    }
    public function lstas()
    {
        return $this->hasMany(Lsta::class, 'event_sidang_id');
    }
    public function sidangs()
    {
        return $this->hasMany(Sidang::class, 'event_sidang_id');
    }
}