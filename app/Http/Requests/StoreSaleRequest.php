<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Form Request untuk pembuatan transaksi penjualan baru.
 *
 * Otorisasi : hanya Kasir.
 * Validasi  : minimal 1 item, setiap produk aktif dan terdaftar, qty ≥ 1.
 *
 * Catatan: unit_price TIDAK diambil dari request — diambil dari DB (selling_price)
 * di SaleService untuk mencegah manipulasi harga dari sisi klien.
 */
class StoreSaleRequest extends FormRequest
{
    /**
     * Hanya Kasir yang dapat membuat transaksi penjualan baru.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'kasir' || 'admin';
    }

    /**
     * Aturan validasi dasar (format & keberadaan).
     */
    public function rules(): array
    {
        return [
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Validasi tambahan: setiap produk harus berstatus aktif.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->any()) {
                    return;
                }

                $items      = $this->input('items', []);
                $productIds = array_unique(array_column($items, 'product_id'));

                $inactiveProducts = Product::whereIn('id', $productIds)
                    ->where('status', '!=', 'active')
                    ->pluck('name', 'id');

                if ($inactiveProducts->isNotEmpty()) {
                    $names = $inactiveProducts->implode(', ');
                    $validator->errors()->add(
                        'items',
                        "Produk berikut tidak aktif dan tidak dapat dijual: {$names}."
                    );
                }
            },
        ];
    }

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'items.required'             => 'Transaksi harus memiliki minimal 1 item.',
            'items.array'                => 'Format item tidak valid.',
            'items.min'                  => 'Transaksi harus memiliki minimal 1 item.',
            'items.*.product_id.required' => 'Produk pada baris :position wajib dipilih.',
            'items.*.product_id.integer' => 'ID produk tidak valid.',
            'items.*.product_id.exists'  => 'Produk pada baris :position tidak ditemukan.',
            'items.*.quantity.required'  => 'Jumlah pada baris :position wajib diisi.',
            'items.*.quantity.integer'   => 'Jumlah harus berupa bilangan bulat.',
            'items.*.quantity.min'       => 'Jumlah pada baris :position minimal 1 unit.',
        ];
    }
}
