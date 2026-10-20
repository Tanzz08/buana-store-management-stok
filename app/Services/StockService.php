<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\StockInRecorded;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockIn;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use App\Repositories\Contracts\StockInRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Service layer untuk operasi yang mengubah stok produk:
 * - Stok Masuk (stock-in dari pemasok)
 *
 * Semua operasi perubahan stok dibungkus dalam DB transaction
 * untuk menjamin atomisitas.
 */
class StockService
{
    public function __construct(
        private readonly StockInRepositoryInterface          $stockInRepository,
        private readonly StockAdjustmentRepositoryInterface  $stockAdjustmentRepository,
        private readonly NotificationRepositoryInterface     $notificationRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Stock Adjustment (Stok Opname)
    // -------------------------------------------------------------------------

    /**
     * Sesuaikan stok produk ke nilai aktual dan catat riwayat penyesuaian secara atomik.
     *
     * Proses:
     * 1. Lock produk dengan SELECT FOR UPDATE untuk menghindari race condition
     * 2. Catat stok sebelum penyesuaian
     * 3. Update `current_stock` produk ke nilai aktual yang diberikan
     * 4. Hitung selisih (difference = actualStock − stockBefore)
     * 5. Buat record `stock_adjustments`
     * 6. Cek dan buat/resolve notifikasi stok rendah sesuai kondisi stok baru
     *
     * @param  int    $productId    ID produk yang akan disesuaikan stoknya
     * @param  int    $actualStock  Jumlah stok aktual hasil perhitungan fisik (0–999.999)
     * @param  string $reason       Alasan penyesuaian (maks 255 karakter)
     * @param  int    $userId       ID pengguna yang melakukan penyesuaian
     *
     * @throws \Throwable jika transaction gagal
     */
    public function adjustStock(int $productId, int $actualStock, string $reason, int $userId): StockAdjustment
    {
        return DB::transaction(function () use ($productId, $actualStock, $reason, $userId): StockAdjustment {
            // Lock produk untuk menghindari race condition
            $product = Product::lockForUpdate()->findOrFail($productId);
            $before  = $product->current_stock;

            // Update stok ke nilai aktual
            $product->update(['current_stock' => $actualStock]);

            // Buat catatan penyesuaian stok
            $adjustment = $this->stockAdjustmentRepository->create([
                'product_id'   => $productId,
                'user_id'      => $userId,
                'stock_before' => $before,
                'stock_after'  => $actualStock,
                'difference'   => $actualStock - $before,
                'reason'       => $reason,
                'adjusted_at'  => now(),
            ]);

            // Cek notifikasi stok rendah setelah penyesuaian
            if ($actualStock <= $product->minimum_stock) {
                $this->notificationRepository->createIfNotExists($productId, $actualStock);
            } else {
                $this->notificationRepository->resolveIfExists($productId);
            }

            return $adjustment;
        });
    }

    /**
     * Perbarui catatan penyesuaian stok dan sinkronisasi perubahan stok produk secara atomik.
     *
     * @param  StockAdjustment $stockAdjustment  Model penyesuaian stok yang akan diubah
     * @param  int             $productId        ID produk
     * @param  int             $actualStock      Jumlah stok fisik aktual baru
     * @param  string          $reason           Alasan penyesuaian baru
     *
     * @throws ValidationException jika stok menjadi negatif
     * @throws \Throwable jika transaction gagal
     */
    public function updateStockAdjustment(
        StockAdjustment $stockAdjustment,
        int $productId,
        int $actualStock,
        string $reason,
    ): StockAdjustment {
        return DB::transaction(function () use ($stockAdjustment, $productId, $actualStock, $reason): StockAdjustment {
            if ($stockAdjustment->product_id === $productId) {
                // Produk sama: hitung perubahan terhadap stock_after sebelumnya
                $product = Product::lockForUpdate()->findOrFail($productId);
                $diffStockAfter = $actualStock - $stockAdjustment->stock_after;
                $newCurrentStock = $product->current_stock + $diffStockAfter;

                if ($newCurrentStock < 0) {
                    throw ValidationException::withMessages([
                        'stock_after' => 'Perubahan stok tidak dapat diterapkan karena akan menyebabkan stok produk saat ini menjadi negatif (' . $newCurrentStock . ').',
                    ]);
                }

                $product->update(['current_stock' => $newCurrentStock]);

                $newDifference = $actualStock - $stockAdjustment->stock_before;

                $this->stockAdjustmentRepository->update($stockAdjustment, [
                    'stock_after' => $actualStock,
                    'difference'  => $newDifference,
                    'reason'      => $reason,
                ]);

                // Cek notifikasi stok rendah setelah pembaruan
                if ($newCurrentStock <= $product->minimum_stock) {
                    $this->notificationRepository->createIfNotExists($product->id, $newCurrentStock);
                } else {
                    $this->notificationRepository->resolveIfExists($product->id);
                }
            } else {
                // Produk berubah: batalkan dampak pada produk lama, lalu terapkan pada produk baru
                $oldProduct = Product::lockForUpdate()->findOrFail($stockAdjustment->product_id);
                $newProduct = Product::lockForUpdate()->findOrFail($productId);

                $revertedOldStock = $oldProduct->current_stock - $stockAdjustment->difference;
                if ($revertedOldStock < 0) {
                    throw ValidationException::withMessages([
                        'product_id' => 'Tidak dapat memindahkan produk karena akan menyebabkan stok produk sebelumnya menjadi negatif (' . $revertedOldStock . ').',
                    ]);
                }

                $oldProduct->update(['current_stock' => $revertedOldStock]);

                if ($revertedOldStock <= $oldProduct->minimum_stock) {
                    $this->notificationRepository->createIfNotExists($oldProduct->id, $revertedOldStock);
                } else {
                    $this->notificationRepository->resolveIfExists($oldProduct->id);
                }

                $newStockBefore = $newProduct->current_stock;
                $newDifference  = $actualStock - $newStockBefore;
                $newProduct->update(['current_stock' => $actualStock]);

                if ($actualStock <= $newProduct->minimum_stock) {
                    $this->notificationRepository->createIfNotExists($newProduct->id, $actualStock);
                } else {
                    $this->notificationRepository->resolveIfExists($newProduct->id);
                }

                $this->stockAdjustmentRepository->update($stockAdjustment, [
                    'product_id'   => $productId,
                    'stock_before' => $newStockBefore,
                    'stock_after'  => $actualStock,
                    'difference'   => $newDifference,
                    'reason'       => $reason,
                ]);
            }

            return $stockAdjustment->fresh(['product', 'user']);
        });
    }

    /**
     * Hapus catatan penyesuaian stok dan kembalikan stok produk ke keadaan sebelum penyesuaian secara atomik.
     *
     * @param  StockAdjustment $stockAdjustment
     *
     * @throws \RuntimeException jika stok produk menjadi negatif
     * @throws \Throwable jika transaction gagal
     */
    public function deleteStockAdjustment(StockAdjustment $stockAdjustment): void
    {
        DB::transaction(function () use ($stockAdjustment): void {
            $product = Product::lockForUpdate()->findOrFail($stockAdjustment->product_id);

            // Batalkan penyesuaian stok:
            // Karena saat penyesuaian: current_stock diubah sebesar difference (= stock_after - stock_before),
            // maka untuk membatalkan penyesuaian: current_stock dikurangi difference.
            $revertedStock = $product->current_stock - $stockAdjustment->difference;

            if ($revertedStock < 0) {
                throw new \RuntimeException(
                    "Penyesuaian stok tidak dapat dihapus karena akan menyebabkan stok {$product->name} menjadi negatif ({$revertedStock})."
                );
            }

            $product->update(['current_stock' => $revertedStock]);

            // Cek notifikasi stok rendah setelah pembatalan penyesuaian
            if ($revertedStock <= $product->minimum_stock) {
                $this->notificationRepository->createIfNotExists($product->id, $revertedStock);
            } else {
                $this->notificationRepository->resolveIfExists($product->id);
            }

            $this->stockAdjustmentRepository->delete($stockAdjustment);
        });
    }

    // -------------------------------------------------------------------------
    // Stock-In
    // -------------------------------------------------------------------------

    /**
     * Catat stok masuk baru dan perbarui stok produk secara atomik.
     *
     * Proses:
     * 1. Buat record `stock_ins` dengan nomor referensi unik
     * 2. Increment `products.current_stock` menggunakan DB::table()->increment()
     *    untuk menghindari race condition
     * 3. Cek apakah stok kini di atas minimum — jika ya, resolve notifikasi stok rendah
     * 4. Fire event StockInRecorded setelah transaction sukses
     *
     * @param  array{
     *     product_id:  int,
     *     supplier_id: int,
     *     quantity:    int,
     *     unit_price:  float|string,
     *     received_at: string,
     * } $data
     *
     * @throws \Throwable jika transaction gagal
     */
    public function recordStockIn(array $data): StockIn
    {
        $stockIn = DB::transaction(function () use ($data): StockIn {
            // Hitung total nilai dengan presisi DECIMAL(15,2)
            $totalValue = round((float) $data['quantity'] * (float) $data['unit_price'], 2);

            $stockIn = $this->stockInRepository->create([
                'reference_number' => $this->generateReference(),
                'product_id'       => $data['product_id'],
                'supplier_id'      => $data['supplier_id'],
                'user_id'          => auth()->id(),
                'quantity'         => $data['quantity'],
                'unit_price'       => $data['unit_price'],
                'total_value'      => $totalValue,
                'received_at'      => $data['received_at'],
            ]);

            // Increment stok secara atomik untuk menghindari race condition
            DB::table('products')
                ->where('id', $data['product_id'])
                ->increment('current_stock', $data['quantity']);

            // Ambil data produk terbaru setelah increment
            $product = Product::find($data['product_id']);

            // Jika stok kembali di atas minimum, resolve notifikasi stok rendah aktif
            if ($product !== null && $product->current_stock > $product->minimum_stock) {
                $this->notificationRepository->resolveIfExists($product->id);
            }

            return $stockIn;
        });

        // Fire event setelah transaction berhasil (di luar transaction)
        event(new StockInRecorded($stockIn));

        return $stockIn;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Generate nomor referensi stok masuk yang unik.
     *
     * Format: SI + YYYYMMDD + 10 karakter random alfanumerik uppercase
     * Contoh: SI20240123AB3XY99012
     * Panjang maksimal: 20 karakter
     */
    private function generateReference(): string
    {
        do {
            $ref = 'SI' . now()->format('Ymd') . strtoupper(Str::random(10));
        } while (StockIn::where('reference_number', $ref)->exists());

        return $ref;
    }
}
