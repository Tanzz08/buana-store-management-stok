<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockAdjustmentRequest;
use App\Http\Requests\UpdateStockAdjustmentRequest;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Repositories\Contracts\StockAdjustmentRepositoryInterface;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Controller Penyesuaian Stok (Stok Opname) untuk peran Admin.
 *
 * Menyediakan riwayat, form pencatatan, dan detail penyesuaian stok.
 */
class StockAdjustmentController extends Controller
{
    public function __construct(
        private readonly StockService                        $stockService,
        private readonly StockAdjustmentRepositoryInterface  $stockAdjustmentRepository,
    ) {}

    /**
     * Tampilkan riwayat penyesuaian stok dengan filter produk dan rentang tanggal.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StockAdjustment::class);

        $productId = $request->query('product_id') ? (int) $request->query('product_id') : null;
        $startDate = $request->query('start_date') ?: null;
        $endDate   = $request->query('end_date') ?: null;

        $adjustments = $this->stockAdjustmentRepository
            ->filter($productId, $startDate, $endDate)
            ->with(['product', 'user'])
            ->latest('adjusted_at')
            ->paginate(20)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.stock-adjustments.index', compact(
            'adjustments',
            'products',
            'productId',
            'startDate',
            'endDate',
        ));
    }

    /**
     * Tampilkan form pencatatan penyesuaian stok baru.
     */
    public function create(): View
    {
        $this->authorize('create', StockAdjustment::class);

        $products = Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'current_stock']);

        return view('admin.stock-adjustments.create', compact('products'));
    }

    /**
     * Simpan catatan penyesuaian stok dan perbarui stok produk.
     */
    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $this->authorize('create', StockAdjustment::class);

        $validated = $request->validated();

        $adjustment = $this->stockService->adjustStock(
            productId:   $validated['product_id'],
            actualStock: $validated['stock_after'],
            reason:      $validated['reason'],
            userId:      $request->user()->id,
        );

        return redirect()
            ->route('admin.stock-adjustments.show', $adjustment)
            ->with('success', 'Penyesuaian stok berhasil dicatat.');
    }

    /**
     * Tampilkan detail catatan penyesuaian stok.
     */
    public function show(StockAdjustment $stockAdjustment): View
    {
        $this->authorize('view', $stockAdjustment);

        $stockAdjustment->load(['product.category', 'user']);

        return view('admin.stock-adjustments.show', compact('stockAdjustment'));
    }

    /**
     * Tampilkan form edit penyesuaian stok.
     */
    public function edit(StockAdjustment $stockAdjustment): View
    {
        $this->authorize('update', $stockAdjustment);

        $stockAdjustment->load(['product']);

        $products = Product::active()
            ->orWhere('id', $stockAdjustment->product_id)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'current_stock']);

        return view('admin.stock-adjustments.edit', compact('stockAdjustment', 'products'));
    }

    /**
     * Perbarui catatan penyesuaian stok dan sinkronisasi stok produk.
     */
    public function update(UpdateStockAdjustmentRequest $request, StockAdjustment $stockAdjustment): RedirectResponse
    {
        $this->authorize('update', $stockAdjustment);

        $validated = $request->validated();

        try {
            $this->stockService->updateStockAdjustment(
                stockAdjustment: $stockAdjustment,
                productId:       (int) $validated['product_id'],
                actualStock:     (int) $validated['stock_after'],
                reason:          $validated['reason'],
            );
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('admin.stock-adjustments.index')
            ->with('success', 'Penyesuaian stok berhasil diperbarui.');
    }

    /**
     * Hapus catatan penyesuaian stok dan kembalikan stok produk.
     */
    public function destroy(StockAdjustment $stockAdjustment): RedirectResponse
    {
        $this->authorize('delete', $stockAdjustment);

        try {
            $this->stockService->deleteStockAdjustment($stockAdjustment);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.stock-adjustments.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.stock-adjustments.index')
            ->with('success', 'Penyesuaian stok berhasil dihapus.');
    }
}
