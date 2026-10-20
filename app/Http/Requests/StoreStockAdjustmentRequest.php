<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request untuk pencatatan penyesuaian stok (stok opname).
 *
 * Otorisasi: Admin dan Gudang.
 * Validasi: produk aktif terdaftar, jumlah stok aktual 0–999.999, alasan maks 255 karakter.
 */
class StoreStockAdjustmentRequest extends FormRequest
{
    /**
     * Hanya Admin dan Gudang yang dapat melakukan penyesuaian stok.
     */
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'gudang'], true);
    }

    /**
     * Aturan validasi untuk penyesuaian stok.
     */
    public function rules(): array
    {
        return [
            'product_id'  => [
                'required',
                'integer',
                'exists:products,id',
                Rule::exists('products', 'id')->where('status', 'active'),
            ],
            'stock_after' => ['required', 'integer', 'min:0', 'max:999999'],
            'reason'      => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'product_id.required'   => 'Produk wajib dipilih.',
            'product_id.integer'    => 'Produk tidak valid.',
            'product_id.exists'     => 'Produk yang dipilih tidak terdaftar atau tidak aktif dalam sistem.',
            'stock_after.required'  => 'Jumlah stok aktual wajib diisi.',
            'stock_after.integer'   => 'Jumlah stok aktual harus berupa bilangan bulat.',
            'stock_after.min'       => 'Jumlah stok aktual tidak boleh negatif.',
            'stock_after.max'       => 'Jumlah stok aktual tidak boleh melebihi 999.999.',
            'reason.required'       => 'Alasan penyesuaian wajib diisi.',
            'reason.string'         => 'Alasan penyesuaian tidak valid.',
            'reason.max'            => 'Alasan penyesuaian tidak boleh melebihi 255 karakter.',
        ];
    }

    /**
     * Label atribut dalam Bahasa Indonesia untuk pesan error default.
     */
    public function attributes(): array
    {
        return [
            'product_id'  => 'produk',
            'stock_after' => 'jumlah stok aktual',
            'reason'      => 'alasan penyesuaian',
        ];
    }
}
