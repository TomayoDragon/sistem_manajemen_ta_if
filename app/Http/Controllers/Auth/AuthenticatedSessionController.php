<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // --- MULAI LOGIKA REDIRECT BERBASIS PERAN ---

        $user = Auth::user();

        if ($user->mahasiswa_id) {
            return redirect()->intended('/mahasiswa/dashboard');

        } elseif ($user->dosen_id) {
            return redirect()->intended('/dosen/dashboard');

        } elseif ($user->staff_id) {
            return redirect()->intended('/staff/dashboard');

        } elseif ($user->admin_id) {
            return redirect()->intended('/admin/dashboard');
        }

        // --- SELESAI LOGIKA REDIRECT ---

        // Fallback jika user login tapi tidak punya peran (seharusnya tidak terjadi)
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->withErrors([
            'login_id' => 'Akun Anda valid, tetapi tidak memiliki peran yang terdaftar.'
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
