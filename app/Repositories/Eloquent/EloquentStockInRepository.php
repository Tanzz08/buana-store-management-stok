<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\StockIn;
use App\Repositories\Contracts\StockInRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class EloquentStockInRepository implements StockInRepositoryInterface
{
    /**
     * Persist a new stock-in record.
     */
    public function create(array $data): StockIn
    {
        return StockIn::create($data);
    }

    /**
     * Find a stock-in record by its primary key.
     */
    public function find(int $id): ?StockIn
    {
        return StockIn::find($id);
    }

    /**
     * Return a query builder for stock-in records, optionally filtered.
     */
    public function filter(
        ?int $productId = null,
        ?int $supplierId = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): Builder {
        $query = StockIn::query();

        if ($productId !== null) {
            $query->where('product_id', $productId);
        }

        if ($supplierId !== null) {
            $query->where('supplier_id', $supplierId);
        }

        if ($startDate !== null) {
            $query->whereDate('received_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->whereDate('received_at', '<=', $endDate);
        }

        return $query;
    }
}
