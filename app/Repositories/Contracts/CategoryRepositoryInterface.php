<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Find a category by name using a case-insensitive comparison.
     *
     * @param  string   $name       The category name to look up.
     * @param  int|null $excludeId  Optional category ID to exclude (useful during updates).
     */
    public function findByName(string $name, ?int $excludeId = null): ?Category;

    /**
     * Determine whether a category still has at least one active product.
     *
     * Used to prevent deletion of categories that are still in use.
     */
    public function hasActiveProducts(int $categoryId): bool;

    /**
     * Retrieve all categories along with a count of their associated products.
     *
     * Each item in the collection will have an additional `products_count` attribute.
     *
     * @return Collection<int, Category>
     */
    public function allWithProductCount(): Collection;
}
