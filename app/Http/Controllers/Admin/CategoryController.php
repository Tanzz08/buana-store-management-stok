<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\EntityHasDependenciesException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller resource untuk manajemen Kategori Produk.
 *
 * Hanya dapat diakses oleh Admin (dijaga oleh middleware role:admin
 * dan CategoryPolicy).
 */
class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService,
    ) {}

    /**
     * Tampilkan daftar semua kategori beserta jumlah produk.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = $this->categoryService->all();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Tampilkan form tambah kategori baru.
     */
    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.create');
    }

    /**
     * Simpan kategori baru ke database.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        try {
            $this->categoryService->create($request->validated());
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit kategori.
     */
    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Perbarui data kategori.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        try {
            $this->categoryService->update($category->id, $request->validated());
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus kategori jika tidak memiliki produk aktif.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        try {
            $this->categoryService->delete($category->id);
        } catch (EntityHasDependenciesException $e) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}
