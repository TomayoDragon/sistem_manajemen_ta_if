<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException; // <-- 1. IMPORT CLASS ERROR
use Illuminate\Support\Facades\Auth; // <-- 2. IMPORT AUTH

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        $middleware->redirectUsersTo(function ($request) {
            $user = Auth::user();
            if ($user->hasRole('mahasiswa')) {
                return route('mahasiswa.dashboard');
            }
            if ($user->hasRole('dosen')) {
                return route('dosen.dashboard');
            }
            if ($user->hasRole('staff')) {
                return route('staff.dashboard');
            }
            if ($user->hasRole('admin')) {
                return route('admin.dashboard');
            }
            return route('login');
        });

    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        $exceptions->renderable(function (TokenMismatchException $e, $request) {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
        
        $exceptions->reportable(function (Throwable $e) {
            //
        });

    })->create();