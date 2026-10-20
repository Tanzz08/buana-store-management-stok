<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Tangani request masuk.
     *
     * Middleware ini memeriksa apakah pengguna yang sudah login memiliki peran
     * yang sesuai dengan yang disyaratkan oleh route. Jika tidak, pengguna
     * diarahkan ke dasbor sesuai perannya sendiri, bukan ke halaman 403 generik.
     *
     * Contoh penggunaan di routes/web.php:
     *   Route::middleware(['auth', 'role:admin'])->group(...)
     *   Route::middleware(['auth', 'role:kasir,admin'])->group(...)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Pastikan pengguna sudah login dan akunnya aktif
        if (! $user || ! $user->is_active) {
            return redirect()->route('login');
        }

        // Periksa apakah role pengguna ada di daftar role yang diizinkan
        if (! in_array($user->role, $roles, true)) {
            return $this->redirectToDashboard($user->role);
        }

        return $next($request);
    }

    /**
     * Arahkan pengguna ke dasbor yang sesuai dengan perannya.
     */
    private function redirectToDashboard(string $role): \Illuminate\Http\RedirectResponse
    {
        $dashboardRoutes = [
            'admin'  => '/admin/dashboard',
            'kasir'  => '/kasir/dashboard',
            'gudang' => '/gudang/dashboard',
        ];

        $target = $dashboardRoutes[$role] ?? '/dashboard';

        return redirect($target)
            ->with('error', 'Anda tidak memiliki izin untuk mengakses halaman tersebut.');
    }
}
