<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk pembuatan kategori baru.
 *
 * Otorisasi: hanya Admin.
 * Validasi: nama unik case-insensitive, maks 100 karakter;
 *           deskripsi opsional, maks 500 karakter.
 */
class StoreCategoryRequest extends FormRequest
{
    /**
     * Hanya Admin yang dapat membuat kategori baru.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi untuk pembuatan kategori.
     *
     * Catatan: keunikan case-insensitive (LOWER) ditangani di CategoryService
     * agar logika bisnis tetap berada di service layer. Form Request ini
     * hanya memvalidasi format/panjang karakter.
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max'      => 'Nama kategori tidak boleh melebihi 100 karakter.',
            'description.max' => 'Deskripsi tidak boleh melebihi 500 karakter.',
        ];
    }
}
