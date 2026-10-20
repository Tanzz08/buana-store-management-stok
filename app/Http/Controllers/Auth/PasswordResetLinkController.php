<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan form permintaan tautan reset kata sandi.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan reset kata sandi ke email yang dimasukkan.
     *
     * Selalu menampilkan pesan konfirmasi yang sama terlepas dari apakah email
     * terdaftar atau tidak, untuk mencegah enumerasi email (anti-enumeration).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
        ]);

        // Kirim tautan reset — abaikan hasilnya (anti-enumeration)
        Password::sendResetLink($request->only('email'));

        // Selalu tampilkan pesan konfirmasi yang sama
        return back()->with(
            'status',
            'Jika email tersebut terdaftar, kami telah mengirimkan tautan reset kata sandi. Periksa kotak masuk Anda.'
        );
    }
}
