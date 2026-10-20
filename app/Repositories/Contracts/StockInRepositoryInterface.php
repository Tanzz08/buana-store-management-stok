<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\StockIn;
use Illuminate\Database\Eloquent\Builder;

interface StockInRepositoryInterface
{
    /**
     * Persist a new stock-in record.
     *
     * Expected keys: reference_number, product_id, supplier_id, user_id,
     * quantity, unit_price, total_value, received_at.
     *
     * @param  array<string, mixed> $data
     */
    public function create(array $data): StockIn;

    /**
     * Find a stock-in record by its primary key.
     */
    public function find(int $id): ?StockIn;

    /**
     * Return a query builder for stock-in records, optionally filtered by
     * product, supplier, and/or date range.
     *
     * Callers are responsible for applying pagination or further constraints.
     *
     * @param  int|null    $productId   FK to `products.id`; null = no filter.
     * @param  int|null    $supplierId  FK to `suppliers.id`; null = no filter.
     * @param  string|null $startDate   ISO-8601 date (inclusive lower bound on `received_at`).
     * @param  string|null $endDate     ISO-8601 date (inclusive upper bound on `received_at`).
     */
    public function filter(
        ?int $productId = null,
        ?int $supplierId = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): Builder;
}
