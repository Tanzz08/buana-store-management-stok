<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\ImportResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Service layer untuk modul Manajemen Produk.
 *
 * Mengelola logika bisnis CRUD produk, termasuk validasi SKU unik,
 * pencatatan riwayat perubahan, serta pencarian produk.
 */
class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /**
     * Ambil semua produk dengan kategori (paginasi 15 per halaman).
     */
    public function all(): LengthAwarePaginator
    {
        return Product::with('category')
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * Buat produk baru dengan stok awal 0.
     *
     * @param  array{
     *     sku: string,
     *     name: string,
     *     category_id: int,
     *     purchase_price: float,
     *     selling_price: float,
     *     unit: string,
     *     minimum_stock: int,
     * } $data
     *
     * @throws ValidationException jika SKU sudah digunakan
     */
    public function create(array $data): Product
    {
        if ($this->productRepository->findBySku($data['sku']) !== null) {
            throw ValidationException::withMessages([
                'sku' => ['SKU sudah digunakan.'],
            ]);
        }

        return Product::create([
            'category_id'    => $data['category_id'],
            'sku'            => $data['sku'],
            'name'           => $data['name'],
            'purchase_price' => $data['purchase_price'],
            'selling_price'  => $data['selling_price'],
            'unit'           => $data['unit'],
            'current_stock'  => 0,
            'minimum_stock'  => $data['minimum_stock'],
            'status'         => 'active',
        ]);
    }

    /**
     * Perbarui data produk. SKU bersifat permanen dan tidak dapat diubah.
     * Setiap perubahan dicatat ke `product_change_logs`.
     *
     * @param  array<string, mixed> $data  Data baru (sku akan diabaikan jika ada)
     *
     * @throws ValidationException jika produk tidak ditemukan (fallthrough ke 404 by findOrFail)
     */
    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);

        // SKU bersifat permanen — hapus dari data jika dikirim
        unset($data['sku']);

        $before = $product->only([
            'category_id', 'name', 'purchase_price',
            'selling_price', 'unit', 'minimum_stock',
        ]);

        $product->update($data);

        $after = $product->fresh()->only([
            'category_id', 'name', 'purchase_price',
            'selling_price', 'unit', 'minimum_stock',
        ]);

        // Catat riwayat perubahan hanya jika ada perbedaan
        if ($before !== $after) {
            $this->productRepository->logChange(
                $product->id,
                (int) auth()->id(),
                $before,
                $after,
            );
        }

        return $product->fresh();
    }

    /**
     * Aktifkan produk (ubah status menjadi 'active').
     */
    public function activate(int $id): void
    {
        $product = Product::findOrFail($id);

        $before = $product->only(['status']);
        $product->update(['status' => 'active']);

        $this->productRepository->logChange(
            $product->id,
            (int) auth()->id(),
            $before,
            ['status' => 'active'],
        );
    }

    /**
     * Nonaktifkan produk (ubah status menjadi 'inactive').
     */
    public function deactivate(int $id): void
    {
        $product = Product::findOrFail($id);

        $before = $product->only(['status']);
        $product->update(['status' => 'inactive']);

        $this->productRepository->logChange(
            $product->id,
            (int) auth()->id(),
            $before,
            ['status' => 'inactive'],
        );
    }

    /**
     * Cari produk berdasarkan kata kunci (nama, SKU, atau nama kategori).
     * Menggunakan index DB untuk memastikan hasil < 500ms.
     *
     * @return Collection<int, Product>
     */
    public function search(string $keyword): Collection
    {
        return $this->productRepository->search($keyword);
    }

    /**
     * Ambil semua kategori aktif untuk dropdown form.
     *
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return Category::orderBy('name')->get();
    }

    /**
     * Impor produk dari baris CSV yang telah di-parse.
     *
     * Baris yang lolos validasi langsung disimpan; baris yang gagal dikumpulkan
     * ke dalam ImportResult::$failures beserta alasan kegagalannya.
     *
     * @param  array<int, array<string, mixed>> $rows  Array asosiatif dari header CSV.
     */
    public function importFromCsv(array $rows): ImportResult
    {
        return $this->productRepository->importFromCsv($rows);
    }
}
