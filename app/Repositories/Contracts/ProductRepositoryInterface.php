<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Repositories\Eloquent\ImportResult;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    /**
     * Find a product by its unique SKU code.
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Search products by keyword across name, SKU, and category name.
     *
     * Implementations must return results in under 500 ms (uses DB indexes
     * on `products.name`, `products.sku`, and `categories.name`).
     *
     * @return Collection<int, Product>
     */
    public function search(string $keyword): Collection;

    /**
     * Record a change to a product's attributes in `product_change_logs`.
     *
     * @param  int   $productId  The product that was modified.
     * @param  int   $userId     The authenticated user making the change.
     * @param  array $before     Snapshot of the product's attributes before the change.
     * @param  array $after      Snapshot of the product's attributes after the change.
     */
    public function logChange(int $productId, int $userId, array $before, array $after): void;

    /**
     * Import products from a parsed CSV row set.
     *
     * Rows that pass validation are persisted; invalid rows are collected into
     * the returned {@see ImportResult::$failures} array with the reason for each
     * failure. Rows with duplicate SKUs are skipped.
     *
     * @param  array<int, array<string, mixed>> $rows  Associative arrays keyed by CSV header names.
     */
    public function importFromCsv(array $rows): ImportResult;
}
