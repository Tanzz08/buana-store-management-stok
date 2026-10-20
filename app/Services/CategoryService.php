<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\EntityHasDependenciesException;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Service layer untuk modul Manajemen Kategori.
 *
 * Mengelola logika bisnis untuk CRUD kategori, termasuk
 * validasi keunikan nama (case-insensitive) dan perlindungan
 * penghapusan kategori yang masih memiliki produk aktif.
 */
class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    /**
     * Ambil semua kategori beserta jumlah produk masing-masing.
     *
     * @return Collection<int, Category>
     */
    public function all(): Collection
    {
        return $this->categoryRepository->allWithProductCount();
    }

    /**
     * Buat kategori baru setelah memvalidasi keunikan nama (case-insensitive).
     *
     * @param  array{name: string, description?: string|null} $data
     * @throws ValidationException jika nama sudah digunakan
     */
    public function create(array $data): Category
    {
        if ($this->categoryRepository->findByName($data['name']) !== null) {
            throw ValidationException::withMessages([
                'name' => ['Nama kategori sudah digunakan.'],
            ]);
        }

        return Category::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Perbarui kategori yang sudah ada setelah memvalidasi keunikan nama
     * (case-insensitive, mengecualikan kategori yang sedang diubah).
     *
     * @param  array{name: string, description?: string|null} $data
     * @throws ValidationException jika nama sudah digunakan oleh kategori lain
     */
    public function update(int $id, array $data): Category
    {
        $category = Category::findOrFail($id);

        if ($this->categoryRepository->findByName($data['name'], $id) !== null) {
            throw ValidationException::withMessages([
                'name' => ['Nama kategori sudah digunakan.'],
            ]);
        }

        $category->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $category->fresh();
    }

    /**
     * Hapus kategori jika tidak memiliki produk aktif.
     *
     * @throws EntityHasDependenciesException jika masih ada produk aktif
     */
    public function delete(int $id): void
    {
        if ($this->categoryRepository->hasActiveProducts($id)) {
            throw new EntityHasDependenciesException(
                'Kategori tidak dapat dihapus karena masih memiliki produk aktif.'
            );
        }

        Category::findOrFail($id)->delete();
    }
}
