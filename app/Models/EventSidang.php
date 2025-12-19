<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSidang extends Model
{
    use HasFactory;
    
    protected $table = 'events_sidangs';
    
    // TAMBAHKAN tanggal_mulai & tanggal_selesai
    protected $fillable = [
        'periode_id', 
        'nama_event', 
        'tipe', 
        'is_published', // Kita anggap ini sebagai flag 'Aktif'
        'tanggal_mulai', 
        'tanggal_selesai'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    // ... relasi lainnya (pengajuanSidangs, dll) tetap sama ...
    public function pengajuanSidangs() { return $this->hasMany(PengajuanSidang::class, 'event_sidang_id'); }
    public function lstas() { return $this->hasMany(Lsta::class, 'event_sidang_id'); }
    public function sidangs() { return $this->hasMany(Sidang::class, 'event_sidang_id'); }
}