<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Hanya Admin yang dapat membuat pengguna baru.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi untuk pembuatan pengguna baru.
     */
    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
            'role'                  => ['required', 'in:admin,kasir,gudang'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'name.required'                  => 'Nama wajib diisi.',
            'name.max'                       => 'Nama tidak boleh melebihi 255 karakter.',
            'email.required'                 => 'Alamat email wajib diisi.',
            'email.email'                    => 'Format alamat email tidak valid.',
            'email.unique'                   => 'Alamat email sudah terdaftar.',
            'password.required'              => 'Kata sandi wajib diisi.',
            'password.min'                   => 'Kata sandi minimal 8 karakter.',
            'password.confirmed'             => 'Konfirmasi kata sandi tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi kata sandi wajib diisi.',
            'role.required'                  => 'Peran pengguna wajib dipilih.',
            'role.in'                        => 'Peran pengguna tidak valid.',
        ];
    }
}
