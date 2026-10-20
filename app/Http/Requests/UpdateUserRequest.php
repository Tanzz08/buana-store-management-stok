<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Hanya Admin yang dapat memperbarui data pengguna.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi untuk pembaruan data pengguna.
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role'      => ['required', 'in:admin,kasir,gudang'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'name.required'  => 'Nama wajib diisi.',
            'name.max'       => 'Nama tidak boleh melebihi 255 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
            'email.unique'   => 'Alamat email sudah digunakan oleh pengguna lain.',
            'role.required'  => 'Peran pengguna wajib dipilih.',
            'role.in'        => 'Peran pengguna tidak valid.',
            'is_active.boolean' => 'Status aktif tidak valid.',
        ];
    }
}
