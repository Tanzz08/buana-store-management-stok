<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk pembuatan produk baru.
 *
 * Otorisasi: Admin saja (sesuai ProductPolicy::create).
 * Validasi: SKU unik, harga > 0, stok_minimum ≥ 1.
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Hanya Admin yang dapat membuat produk baru.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Aturan validasi untuk pembuatan produk baru.
     */
    public function rules(): array
    {
        return [
            'sku'            => ['required', 'string', 'max:100', 'unique:products,sku'],
            'name'           => ['required', 'string', 'max:255'],
            'category_id'    => ['required', 'integer', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0.01'],
            'selling_price'  => ['required', 'numeric', 'min:0.01'],
            'unit'           => ['required', 'string', 'max:50'],
            'minimum_stock'  => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'sku.required'            => 'SKU wajib diisi.',
            'sku.max'                 => 'SKU tidak boleh melebihi 100 karakter.',
            'sku.unique'              => 'SKU sudah digunakan.',
            'name.required'           => 'Nama produk wajib diisi.',
            'name.max'                => 'Nama produk tidak boleh melebihi 255 karakter.',
            'category_id.required'    => 'Kategori wajib dipilih.',
            'category_id.exists'      => 'Kategori yang dipilih tidak valid.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric'  => 'Harga beli harus berupa angka.',
            'purchase_price.min'      => 'Harga beli harus lebih dari 0.',
            'selling_price.required'  => 'Harga jual wajib diisi.',
            'selling_price.numeric'   => 'Harga jual harus berupa angka.',
            'selling_price.min'       => 'Harga jual harus lebih dari 0.',
            'unit.required'           => 'Satuan wajib diisi.',
            'unit.max'                => 'Satuan tidak boleh melebihi 50 karakter.',
            'minimum_stock.required'  => 'Stok minimum wajib diisi.',
            'minimum_stock.integer'   => 'Stok minimum harus berupa bilangan bulat.',
            'minimum_stock.min'       => 'Stok minimum harus minimal 1.',
        ];
    }

    /**
     * Nama atribut dalam Bahasa Indonesia untuk pesan error default.
     */
    public function attributes(): array
    {
        return [
            'sku'            => 'SKU',
            'name'           => 'Nama produk',
            'category_id'    => 'Kategori',
            'purchase_price' => 'Harga beli',
            'selling_price'  => 'Harga jual',
            'unit'           => 'Satuan',
            'minimum_stock'  => 'Stok minimum',
        ];
    }
}
