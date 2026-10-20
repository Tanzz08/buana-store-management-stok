<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Tampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses percobaan login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Catat aktivitas login
        $user = Auth::user();
        $this->authService->logActivity($user->id, 'login', $request->ip());

        // Arahkan ke dasbor sesuai peran pengguna
        return redirect($this->dashboardByRole($user->role));
    }

    /**
     * Proses logout dan hapus sesi aktif.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            $this->authService->logActivity($user->id, 'logout', $request->ip());
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Kembalikan URL dasbor berdasarkan peran pengguna.
     */
    private function dashboardByRole(string $role): string
    {
        return match ($role) {
            'admin'  => '/admin/dashboard',
            'kasir'  => '/kasir/dashboard',
            'gudang' => '/gudang/dashboard',
            default  => '/dashboard',
        };
    }
}
