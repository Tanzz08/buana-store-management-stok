<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller resource untuk manajemen Produk oleh Admin.
 *
 * Admin memiliki akses penuh: CRUD, aktifkan/nonaktifkan, dan pencarian produk.
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * Tampilkan daftar semua produk dengan kategori dan pencarian.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $keyword  = $request->query('q', '');
        $products = $keyword
            ? $this->productService->search((string) $keyword)
            : $this->productService->all();

        return view('admin.products.index', compact('products', 'keyword'));
    }

    /**
     * Tampilkan form tambah produk baru.
     */
    public function create(): View
    {
        $this->authorize('create', Product::class);

        $categories = $this->productService->getCategories();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Simpan produk baru ke database.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        try {
            $this->productService->create($request->validated());
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit produk.
     */
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $categories = $this->productService->getCategories();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Perbarui data produk.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->productService->update($product->id, $request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Nonaktifkan produk (deactivate via status, bukan hard delete).
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deactivate($product->id);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dinonaktifkan.');
    }

    /**
     * Aktifkan produk.
     */
    public function activate(Product $product): RedirectResponse
    {
        $this->authorize('updateStatus', $product);

        $this->productService->activate($product->id);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diaktifkan.');
    }

    /**
     * Nonaktifkan produk secara eksplisit.
     */
    public function deactivate(Product $product): RedirectResponse
    {
        $this->authorize('updateStatus', $product);

        $this->productService->deactivate($product->id);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dinonaktifkan.');
    }

    /**
     * Tampilkan form unggah CSV untuk impor produk massal.
     */
    public function importForm(): View
    {
        $this->authorize('create', Product::class);

        return view('admin.products.import');
    }

    /**
     * Proses file CSV yang diunggah dan impor produk.
     *
     * Hanya baris yang lolos validasi yang disimpan; baris gagal
     * dikembalikan ke view beserta alasan kegagalannya.
     */
    public function import(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ], [
            'csv_file.required' => 'File CSV wajib diunggah.',
            'csv_file.file'     => 'Unggahan harus berupa file.',
            'csv_file.mimes'    => 'File harus berformat CSV (.csv atau .txt).',
            'csv_file.max'      => 'Ukuran file tidak boleh melebihi 2 MB.',
        ]);

        $path = $request->file('csv_file')->getRealPath();

        // Parse CSV: first row = headers, remaining rows = data
        $rows    = [];
        $headers = null;

        if (($handle = fopen($path, 'r')) !== false) {
            while (($line = fgetcsv($handle)) !== false) {
                if ($headers === null) {
                    // Normalise headers: lowercase + trim whitespace
                    $headers = array_map(
                        fn (string $h) => strtolower(trim($h)),
                        $line
                    );
                    continue;
                }

                // Skip completely blank lines
                if (count(array_filter($line, fn ($v) => trim($v) !== '')) === 0) {
                    continue;
                }

                // Pad or trim data row to match header count
                $line = array_slice(array_pad($line, count($headers), ''), 0, count($headers));
                $rows[] = array_combine($headers, $line);
            }
            fclose($handle);
        }

        if (empty($rows)) {
            return back()->with('import_error', 'File CSV tidak mengandung data (selain baris header).');
        }

        $result = $this->productService->importFromCsv($rows);

        return redirect()
            ->route('admin.products.import.form')
            ->with('import_success', $result->successCount)
            ->with('import_failures', $result->failures);
    }
}
