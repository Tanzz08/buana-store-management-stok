<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\StockAdjustment;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class EloquentStockAdjustmentRepository implements StockAdjustmentRepositoryInterface
{
    /**
     * Persist a new stock-adjustment record.
     */
    public function create(array $data): StockAdjustment
    {
        return StockAdjustment::create($data);
    }

    /**
     * Return a query builder for stock-adjustment records, optionally filtered.
     */
    public function filter(
        ?int $productId = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): Builder {
        $query = StockAdjustment::query();

        if ($productId !== null) {
            $query->where('product_id', $productId);
        }

        if ($startDate !== null) {
            $query->whereDate('adjusted_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->whereDate('adjusted_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Update an existing stock-adjustment record.
     */
    public function update(StockAdjustment $stockAdjustment, array $data): bool
    {
        return $stockAdjustment->update($data);
    }

    /**
     * Delete a stock-adjustment record.
     */
    public function delete(StockAdjustment $stockAdjustment): bool
    {
        return (bool) $stockAdjustment->delete();
    }
}
