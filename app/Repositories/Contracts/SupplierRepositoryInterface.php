<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Supplier;
use Illuminate\Support\Collection;

interface SupplierRepositoryInterface
{
    /**
     * Retrieve all suppliers.
     *
     * @return Collection<int, Supplier>
     */
    public function all(): Collection;

    /**
     * Find a supplier by its primary key.
     */
    public function find(int $id): ?Supplier;

    /**
     * Find a supplier by name using an exact, case-sensitive match.
     *
     * @param  int|null $excludeId  Optional ID to exclude (useful during update uniqueness checks).
     */
    public function findByName(string $name, ?int $excludeId = null): ?Supplier;

    /**
     * Persist a new supplier record.
     *
     * @param  array<string, mixed> $data
     */
    public function create(array $data): Supplier;

    /**
     * Update an existing supplier's attributes.
     *
     * @param  array<string, mixed> $data
     */
    public function update(int $id, array $data): Supplier;

    /**
     * Permanently delete a supplier record.
     *
     * Callers must check {@see hasStockIns()} before calling this method
     * to avoid violating foreign-key constraints.
     */
    public function delete(int $id): void;

    /**
     * Determine whether a supplier has any associated stock-in records.
     *
     * Used to prevent deletion of suppliers that still have purchase history.
     */
    public function hasStockIns(int $supplierId): bool;
}
