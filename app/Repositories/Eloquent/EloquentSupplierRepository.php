<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentSupplierRepository implements SupplierRepositoryInterface
{
    /**
     * Retrieve all suppliers.
     */
    public function all(): Collection
    {
        return Supplier::orderBy('name')->get();
    }

    /**
     * Find a supplier by its primary key.
     */
    public function find(int $id): ?Supplier
    {
        return Supplier::find($id);
    }

    /**
     * Find a supplier by name using an exact, case-sensitive match.
     */
    public function findByName(string $name, ?int $excludeId = null): ?Supplier
    {
        $query = Supplier::where('name', $name);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    /**
     * Persist a new supplier record.
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Update an existing supplier's attributes.
     */
    public function update(int $id, array $data): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);

        return $supplier->fresh();
    }

    /**
     * Permanently delete a supplier record.
     */
    public function delete(int $id): void
    {
        Supplier::where('id', $id)->delete();
    }

    /**
     * Determine whether a supplier has any associated stock-in records.
     */
    public function hasStockIns(int $supplierId): bool
    {
        return Supplier::find($supplierId)
            ?->stockIns()
            ->exists() ?? false;
    }
}
