<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductChangeLog;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{
    /**
     * Find a product by its unique SKU code.
     */
    public function findBySku(string $sku): ?Product
    {
        return Product::where('sku', $sku)->first();
    }

    /**
     * Search products by keyword across name, SKU, and category name.
     */
    public function search(string $keyword): Collection
    {
        return Product::with('category')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->where(function ($query) use ($keyword) {
                $query->where('products.name', 'ILIKE', "%{$keyword}%")
                      ->orWhere('products.sku', 'ILIKE', "%{$keyword}%")
                      ->orWhere('categories.name', 'ILIKE', "%{$keyword}%");
            })
            ->select('products.*')
            ->get();
    }

    /**
     * Record a change to a product's attributes in `product_change_logs`.
     */
    public function logChange(int $productId, int $userId, array $before, array $after): void
    {
        ProductChangeLog::create([
            'product_id'  => $productId,
            'user_id'     => $userId,
            'before_data' => $before,
            'after_data'  => $after,
            'created_at'  => now(),
        ]);
    }

    /**
     * Import products from a parsed CSV row set.
     *
     * Required columns: sku, nama, kategori, harga_beli, harga_jual, satuan, stok_minimum
     */
    public function importFromCsv(array $rows): ImportResult
    {
        $result = new ImportResult();
        $requiredColumns = ['sku', 'nama', 'kategori', 'harga_beli', 'harga_jual', 'satuan', 'stok_minimum'];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // 1-based, +1 for header row

            // Normalise keys to lowercase/trimmed
            $row = array_combine(
                array_map('trim', array_keys($row)),
                array_map('trim', array_values($row))
            );

            // Check required columns present and non-empty
            foreach ($requiredColumns as $col) {
                if (!isset($row[$col]) || $row[$col] === '') {
                    $result->addFailure(
                        $rowNumber,
                        $row['sku'] ?? '',
                        "Kolom '{$col}' wajib diisi."
                    );
                    continue 2;
                }
            }

            $sku          = $row['sku'];
            $nama         = $row['nama'];
            $kategori     = $row['kategori'];
            $hargaBeli    = $row['harga_beli'];
            $hargaJual    = $row['harga_jual'];
            $satuan       = $row['satuan'];
            $stokMinimum  = $row['stok_minimum'];

            // Validate harga_beli
            if (!is_numeric($hargaBeli) || (float) $hargaBeli <= 0) {
                $result->addFailure($rowNumber, $sku, 'harga_beli harus berupa angka lebih dari 0.');
                continue;
            }

            // Validate harga_jual
            if (!is_numeric($hargaJual) || (float) $hargaJual <= 0) {
                $result->addFailure($rowNumber, $sku, 'harga_jual harus berupa angka lebih dari 0.');
                continue;
            }

            // Validate stok_minimum
            if (!ctype_digit((string) ltrim((string) $stokMinimum, '-')) || (int) $stokMinimum < 1) {
                $result->addFailure($rowNumber, $sku, 'stok_minimum harus berupa bilangan bulat minimal 1.');
                continue;
            }

            // Validate kategori exists (case-insensitive)
            $category = Category::whereRaw('LOWER(name) = LOWER(?)', [$kategori])->first();
            if ($category === null) {
                $result->addFailure($rowNumber, $sku, "Kategori '{$kategori}' tidak ditemukan.");
                continue;
            }

            // Validate SKU uniqueness
            if (Product::where('sku', $sku)->exists()) {
                $result->addFailure($rowNumber, $sku, "SKU '{$sku}' sudah ada di database.");
                continue;
            }

            // Persist the product
            Product::create([
                'category_id'    => $category->id,
                'sku'            => $sku,
                'name'           => $nama,
                'purchase_price' => (float) $hargaBeli,
                'selling_price'  => (float) $hargaJual,
                'unit'           => $satuan,
                'current_stock'  => 0,
                'minimum_stock'  => (int) $stokMinimum,
                'status'         => 'active',
            ]);

            $result->successCount++;
        }

        return $result;
    }
}
