<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     * @param string $roleName (Contoh: 'mahasiswa', 'dosen')
     * @return bool
     */

    public function hasRole(string $roleName): bool
    {
        switch ($roleName) {
            case 'mahasiswa':
                return $this->mahasiswa_id !== null;
            case 'dosen':
                return $this->dosen_id !== null;
            case 'staff':
                return $this->staff_id !== null;
            case 'admin':
                return $this->admin_id !== null;
            default:
                return false;
        }
    }

    protected $fillable = [
        'login_id',
        'email',
        'password',
        'mahasiswa_id', // Tambahkan foreign key
        'dosen_id',     // Tambahkan foreign key
        'staff_id',     // Tambahkan foreign key
        'admin_id',     // Tambahkan foreign key
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    // =================================================================
    // RELASI KE TABEL PERAN (ROLE)
    // =================================================================

    /**
     * Mendapatkan profil mahasiswa yang terkait dengan user ini.
     */
    public function mahasiswa()
    {
        // Relasi one-to-one (atau one-to-many-reverse)
        // User ini 'milik' satu profil mahasiswa
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    /**
     * Mendapatkan profil dosen yang terkait dengan user ini.
     */
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    /**
     * Mendapatkan profil staff yang terkait dengan user ini.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Mendapatkan profil admin yang terkait dengan user ini.
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}