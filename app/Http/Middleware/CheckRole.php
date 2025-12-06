<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles (Ini akan menangkap semua peran, misal: 'mahasiswa', 'admin')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Ambil user yang sedang login
        $user = Auth::user();

        // 2. Jika tidak ada user ATAU user tidak punya peran yang diizinkan
        if (! $user) {
            return redirect('login');
        }

        // 3. Loop semua peran yang diizinkan untuk rute ini
        foreach ($roles as $role) {
            // 4. Gunakan method hasRole() yang kita buat di Model User
            if ($user->hasRole($role)) {
                // 5. Jika user punya salah satu peran, izinkan akses
                return $next($request);
            }
        }

        // 6. Jika loop selesai dan tidak ada peran yang cocok, tolak akses
        abort(403, 'ANDA TIDAK MEMILIKI AKSES.');
    }
}