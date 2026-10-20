<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Builder;

interface StockAdjustmentRepositoryInterface
{
    /**
     * Persist a new stock-adjustment record.
     *
     * Expected keys: product_id, user_id, stock_before, stock_after,
     * difference, reason, adjusted_at.
     *
     * @param  array<string, mixed> $data
     */
    public function create(array $data): StockAdjustment;

    /**
     * Return a query builder for stock-adjustment records, optionally filtered
     * by product and/or date range.
     *
     * Callers are responsible for applying pagination or further constraints.
     *
     * @param  int|null    $productId  FK to `products.id`; null = no filter.
     * @param  string|null $startDate  ISO-8601 date (inclusive lower bound on `adjusted_at`).
     * @param  string|null $endDate    ISO-8601 date (inclusive upper bound on `adjusted_at`).
     */
    public function filter(
        ?int $productId = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): Builder;

    /**
     * Update an existing stock-adjustment record.
     *
     * @param  StockAdjustment       $stockAdjustment
     * @param  array<string, mixed>  $data
     */
    public function update(StockAdjustment $stockAdjustment, array $data): bool;

    /**
     * Delete a stock-adjustment record.
     *
     * @param  StockAdjustment $stockAdjustment
     */
    public function delete(StockAdjustment $stockAdjustment): bool;
}
