<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CheckSessionValidity
 *
 * Mendeteksi apakah sesi autentikasi telah kedaluwarsa.
 * Jika ya, request AJAX dikembalikan dengan HTTP 401 sehingga
 * komponen Alpine.js di sisi klien dapat menampilkan modal
 * peringatan "sesi telah berakhir" sebelum mengarahkan ke login.
 *
 * Middleware ini TIDAK menggantikan `auth` middleware — ia bekerja
 * bersama dengan `auth` untuk memberikan UX yang lebih baik ketika
 * sesi berakhir di tengah pengisian formulir.
 */
class CheckSessionValidity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya periksa untuk request yang sudah autentikasi
        if (! $request->user()) {
            // Untuk AJAX request, kembalikan 401 agar Alpine.js bisa handle
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message'        => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    'session_expired' => true,
                ], 401);
            }

            // Untuk request biasa, redirect ke login (ditangani oleh auth middleware)
            return $next($request);
        }

        // Tambahkan header untuk memberi tahu sisi klien kapan sesi akan berakhir
        $response = $next($request);

        // Hitung sisa waktu sesi dalam detik
        $sessionLifetime = config('session.lifetime', 60) * 60; // konversi menit ke detik
        $lastActivity    = $request->session()->get('_last_activity', time());
        $timeLeft        = max(0, $lastActivity + $sessionLifetime - time());

        // Perbarui last activity
        $request->session()->put('_last_activity', time());

        // Kirim header sisa waktu ke klien (dalam detik)
        if (method_exists($response, 'header')) {
            $response->header('X-Session-Time-Left', $timeLeft);
        }

        return $response;
    }
}
