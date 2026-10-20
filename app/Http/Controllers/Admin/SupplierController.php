<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\EntityHasDependenciesException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller resource untuk manajemen Pemasok.
 *
 * Hanya dapat diakses oleh Admin (dijaga oleh middleware role:admin
 * dan SupplierPolicy).
 */
class SupplierController extends Controller
{
    public function __construct(
        private readonly SupplierService $supplierService,
    ) {}

    /**
     * Tampilkan daftar semua pemasok.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Supplier::class);

        $suppliers = $this->supplierService->all();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    /**
     * Tampilkan form tambah pemasok baru.
     */
    public function create(): View
    {
        $this->authorize('create', Supplier::class);

        return view('admin.suppliers.create');
    }

    /**
     * Simpan pemasok baru ke database.
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $this->authorize('create', Supplier::class);

        try {
            $this->supplierService->create($request->validated());
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Pemasok berhasil ditambahkan.');
    }

    /**
     * Tampilkan form edit pemasok.
     */
    public function edit(Supplier $supplier): View
    {
        $this->authorize('update', $supplier);

        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Perbarui data pemasok.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);

        try {
            $this->supplierService->update($supplier->id, $request->validated());
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Pemasok berhasil diperbarui.');
    }

    /**
     * Hapus pemasok jika tidak memiliki riwayat stok masuk.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        try {
            $this->supplierService->delete($supplier->id);
        } catch (EntityHasDependenciesException $e) {
            return redirect()
                ->route('admin.suppliers.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Pemasok berhasil dihapus.');
    }
}
