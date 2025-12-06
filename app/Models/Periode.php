<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    use HasFactory;
    protected $fillable = ['nama', 'tahun_akademik', 'semester', 'is_active'];

    public function eventsSidang()
    {
        return $this->hasMany(EventSidang::class, 'periode_id');
    }
    public function tugasAkhirs()
    {
        return $this->hasMany(TugasAkhir::class, 'periode_id');
    }

}