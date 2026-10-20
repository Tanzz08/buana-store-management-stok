<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Sale;
use App\Repositories\Contracts\SaleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EloquentSaleRepository implements SaleRepositoryInterface
{
    /**
     * Persist a new sale together with all its line items inside a single DB transaction.
     */
    public function createWithItems(array $saleData, array $items): Sale
    {
        return DB::transaction(function () use ($saleData, $items): Sale {
            $sale = Sale::create($saleData);

            foreach ($items as $item) {
                $sale->items()->create($item);
            }

            return $sale->load('items');
        });
    }

    /**
     * Generate the next unique invoice number.
     *
     * Format: INV + YYYYMMDD + 6-digit daily sequence (zero-padded).
     * Example: INV20240123000042
     */
    public function generateInvoiceNumber(): string
    {
        return DB::transaction(function (): string {
            $today  = now()->format('Ymd');
            $prefix = "INV{$today}";

            // Lock the latest invoice for today to prevent duplicate numbers under concurrency
            $last = Sale::where('invoice_number', 'LIKE', "{$prefix}%")
                ->lockForUpdate()
                ->orderByDesc('invoice_number')
                ->first();

            if ($last === null) {
                $sequence = 1;
            } else {
                // Extract the 6-digit sequence from the end of the invoice number
                $sequence = (int) substr($last->invoice_number, -6) + 1;
            }

            return $prefix . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Return a query builder for sales, optionally filtered by date range and/or cashier.
     */
    public function filterByDateAndCashier(
        ?string $startDate,
        ?string $endDate,
        ?int $cashierId,
    ): Builder {
        $query = Sale::query();

        if ($startDate !== null) {
            $query->whereDate('sold_at', '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->whereDate('sold_at', '<=', $endDate);
        }

        if ($cashierId !== null) {
            $query->where('user_id', $cashierId);
        }

        return $query;
    }
}
