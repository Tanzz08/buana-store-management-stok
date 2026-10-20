<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk pembaruan data pemasok.
 *
 * Otorisasi: hanya Admin.
 * Validasi: nama unik (ditangani di SupplierService, mengecualikan pemasok saat ini),
 *           maks 100 karakter; telepon 7–15 digit angka; email valid; alamat opsional.
 */
class UpdateSupplierRequest extends FormRequest
{
    /**
     * Hanya Admin yang dapat memperbarui data pemasok.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi untuk pembaruan pemasok.
     *
     * Catatan: keunikan nama (mengecualikan pemasok saat ini) ditangani di
     * SupplierService agar logika bisnis tetap berada di service layer.
     */
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:100'],
            'phone'   => ['nullable', 'string', 'regex:/^\d{7,15}$/'],
            'email'   => ['nullable', 'string', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama pemasok wajib diisi.',
            'name.max'      => 'Nama pemasok tidak boleh melebihi 100 karakter.',
            'phone.regex'   => 'Nomor telepon harus terdiri dari 7 hingga 15 digit angka.',
            'email.email'   => 'Format email tidak valid.',
            'email.max'     => 'Email tidak boleh melebihi 255 karakter.',
            'address.max'   => 'Alamat tidak boleh melebihi 500 karakter.',
        ];
    }
}
