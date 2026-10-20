<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk pencatatan stok masuk baru.
 *
 * Otorisasi: Admin dan Gudang.
 * Validasi: produk & pemasok harus terdaftar, jumlah 1–999.999,
 *           harga beli 0,01–999.999.999,99, tanggal tidak melewati hari ini.
 */
class StoreStockInRequest extends FormRequest
{
    /**
     * Hanya Admin dan Gudang yang dapat mencatat stok masuk.
     */
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'gudang'], true);
    }

    /**
     * Aturan validasi untuk pencatatan stok masuk.
     */
    public function rules(): array
    {
        return [
            'product_id'  => ['required', 'integer', 'exists:products,id'],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:999999'],
            'unit_price'  => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'received_at' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'product_id.required'   => 'Produk wajib dipilih.',
            'product_id.exists'     => 'Produk yang dipilih tidak terdaftar dalam sistem.',
            'supplier_id.required'  => 'Pemasok wajib dipilih.',
            'supplier_id.exists'    => 'Pemasok yang dipilih tidak terdaftar dalam sistem.',
            'quantity.required'     => 'Jumlah unit wajib diisi.',
            'quantity.integer'      => 'Jumlah unit harus berupa bilangan bulat.',
            'quantity.min'          => 'Jumlah harus lebih dari 0.',
            'quantity.max'          => 'Jumlah unit tidak boleh melebihi 999.999.',
            'unit_price.required'   => 'Harga beli per unit wajib diisi.',
            'unit_price.numeric'    => 'Harga beli harus berupa angka.',
            'unit_price.min'        => 'Harga beli per unit harus lebih dari 0.',
            'unit_price.max'        => 'Harga beli per unit tidak boleh melebihi 999.999.999,99.',
            'received_at.required'  => 'Tanggal penerimaan wajib diisi.',
            'received_at.date'      => 'Format tanggal penerimaan tidak valid.',
            'received_at.before_or_equal' => 'Tanggal penerimaan tidak boleh melebihi tanggal hari ini.',
        ];
    }

    /**
     * Label atribut dalam Bahasa Indonesia untuk pesan error default.
     */
    public function attributes(): array
    {
        return [
            'product_id'  => 'produk',
            'supplier_id' => 'pemasok',
            'quantity'    => 'jumlah unit',
            'unit_price'  => 'harga beli per unit',
            'received_at' => 'tanggal penerimaan',
        ];
    }
}
