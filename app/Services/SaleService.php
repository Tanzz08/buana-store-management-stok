<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\Sale;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Service layer untuk modul Penjualan.
 *
 * Menangani pembuatan transaksi penjualan secara atomik:
 * - Lock produk untuk mencegah race condition
 * - Validasi stok mencukupi untuk setiap item
 * - Buat Sale + SaleItems
 * - Decrement stok setiap produk
 * - Periksa dan buat notifikasi stok rendah bila diperlukan
 */
class SaleService
{
    public function __construct(
        private readonly SaleRepositoryInterface         $saleRepository,
        private readonly NotificationRepositoryInterface $notificationRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Create Sale
    // -------------------------------------------------------------------------

    /**
     * Buat transaksi penjualan baru secara atomik.
     *
     * @param  array{user_id?: int}        $data   Data header penjualan (user_id opsional, diambil dari auth jika tidak ada).
     * @param  array<int, array{
     *     product_id: int,
     *     quantity:   int,
     * }>                                  $items  Daftar item yang akan dijual.
     *
     * @throws InsufficientStockException  Jika stok salah satu produk tidak mencukupi.
     * @throws \Throwable                  Jika transaction gagal.
     */
    public function createSale(array $data, array $items): Sale
    {
        $sale = DB::transaction(function () use ($data, $items): Sale {
            // 1. Lock semua produk yang terlibat untuk mencegah race condition
            $productIds = array_column($items, 'product_id');
            $products   = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 2. Validasi stok mencukupi untuk semua item
            foreach ($items as $item) {
                /** @var Product $product */
                $product = $products[$item['product_id']];
                if ($product->current_stock < $item['quantity']) {
                    throw new InsufficientStockException($product->name, $product->current_stock);
                }
            }

            // 3. Persiapkan data item (ambil unit_price dari DB untuk keamanan)
            $preparedItems = [];
            $totalAmount   = 0;

            foreach ($items as $item) {
                /** @var Product $product */
                $product   = $products[$item['product_id']];
                $unitPrice = (float) $product->selling_price;
                $subtotal  = round($item['quantity'] * $unitPrice, 2);

                $preparedItems[] = [
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal'   => $subtotal,
                ];

                $totalAmount += $subtotal;
            }

            // 4. Generate nomor invoice dan buat record Sale
            $invoiceNumber = $this->saleRepository->generateInvoiceNumber();

            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'user_id'        => $data['user_id'] ?? auth()->id(),
                'total_amount'   => round($totalAmount, 2),
                'sold_at'        => now(),
            ]);

            // 5. Buat SaleItem dan decrement stok setiap produk
            foreach ($preparedItems as $item) {
                $sale->items()->create($item);

                DB::table('products')
                    ->where('id', $item['product_id'])
                    ->decrement('current_stock', $item['quantity']);
            }

            // 6. Refresh dan cek notifikasi stok rendah untuk setiap produk terjual
            foreach ($productIds as $productId) {
                $product = Product::find($productId);
                if ($product !== null && $product->current_stock <= $product->minimum_stock) {
                    $this->notificationRepository->createIfNotExists(
                        $product->id,
                        $product->current_stock,
                    );
                }
            }

            return $sale->load('items.product');
        });

        return $sale;
    }
}
