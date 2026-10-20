<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller produk untuk peran Gudang.
 *
 * Gudang hanya dapat melihat daftar produk dan mengedit data produk.
 * Tidak memiliki akses untuk mengaktifkan/menonaktifkan atau membuat produk baru.
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * Tampilkan daftar semua produk dengan fitur pencarian.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $keyword  = $request->query('q', '');
        $products = $keyword
            ? $this->productService->search((string) $keyword)
            : $this->productService->all();

        return view('gudang.products.index', compact('products', 'keyword'));
    }

    /**
     * Tampilkan form edit produk.
     */
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $categories = $this->productService->getCategories();

        return view('gudang.products.edit', compact('product', 'categories'));
    }

    /**
     * Perbarui data produk.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->productService->update($product->id, $request->validated());

        return redirect()
            ->route('gudang.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }
}
