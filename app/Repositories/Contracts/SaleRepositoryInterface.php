<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;

interface SaleRepositoryInterface
{
    /**
     * Persist a new sale together with all its line items inside a single
     * DB transaction.
     *
     * @param  array<string, mixed>            $saleData  Attributes for the `sales` row.
     * @param  array<int, array<string, mixed>> $items     Each element maps to a `sale_items` row
     *                                                     and must include: product_id, quantity,
     *                                                     unit_price, subtotal.
     */
    public function createWithItems(array $saleData, array $items): Sale;

    /**
     * Generate the next unique invoice number.
     *
     * Format: INV + YYYYMMDD + 6-digit daily sequence (zero-padded).
     * Example: INV20240123000042
     *
     * Implementations must use a lock (e.g. `lockForUpdate`) to prevent
     * duplicate numbers under concurrent requests.
     */
    public function generateInvoiceNumber(): string;

    /**
     * Return a query builder for sales, optionally filtered by date range
     * and/or the ID of the cashier (user) who processed the sale.
     *
     * Callers are responsible for applying pagination or further constraints.
     *
     * @param  string|null $startDate  ISO-8601 date string (inclusive lower bound).
     * @param  string|null $endDate    ISO-8601 date string (inclusive upper bound).
     * @param  int|null    $cashierId  FK to `users.id`; null = no cashier filter.
     */
    public function filterByDateAndCashier(
        ?string $startDate,
        ?string $endDate,
        ?int $cashierId,
    ): Builder;
}
