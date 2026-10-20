<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Find a category by name using a case-insensitive comparison.
     */
    public function findByName(string $name, ?int $excludeId = null): ?Category
    {
        $query = Category::whereRaw('LOWER(name) = LOWER(?)', [$name]);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    /**
     * Determine whether a category still has at least one active product.
     */
    public function hasActiveProducts(int $categoryId): bool
    {
        return Category::find($categoryId)
            ?->products()
            ->where('status', 'active')
            ->exists() ?? false;
    }

    /**
     * Retrieve all categories along with a count of their associated products.
     */
    public function allWithProductCount(): Collection
    {
        return Category::withCount('products')
            ->orderBy('name')
            ->get();
    }
}
