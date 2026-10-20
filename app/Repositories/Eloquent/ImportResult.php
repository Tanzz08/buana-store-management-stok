<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

/**
 * Carries the outcome of a CSV product import operation.
 *
 * Valid rows are persisted immediately; invalid rows are collected here
 * so the caller can report them back to the user without aborting the
 * entire import.
 */
final class ImportResult
{
    /**
     * Number of rows that were successfully imported.
     */
    public int $successCount;

    /**
     * Details of rows that failed validation or could not be persisted.
     *
     * Each element is an associative array with at minimum:
     *   - 'row'    (int)    — 1-based row number in the original CSV.
     *   - 'sku'    (string) — SKU value found in the row (may be empty).
     *   - 'reason' (string) — Human-readable explanation of the failure.
     *
     * @var array<int, array{row: int, sku: string, reason: string}>
     */
    public array $failures;

    public function __construct(int $successCount = 0, array $failures = [])
    {
        $this->successCount = $successCount;
        $this->failures     = $failures;
    }

    /**
     * Record a single row failure.
     *
     * @param  int    $row    1-based CSV row number.
     * @param  string $sku    SKU found in the row.
     * @param  string $reason Why the row was rejected.
     */
    public function addFailure(int $row, string $sku, string $reason): void
    {
        $this->failures[] = [
            'row'    => $row,
            'sku'    => $sku,
            'reason' => $reason,
        ];
    }

    /**
     * Returns true when every row was imported without errors.
     */
    public function hasFailures(): bool
    {
        return count($this->failures) > 0;
    }

    /**
     * Total number of rows processed (success + failures).
     */
    public function totalRows(): int
    {
        return $this->successCount + count($this->failures);
    }
}
