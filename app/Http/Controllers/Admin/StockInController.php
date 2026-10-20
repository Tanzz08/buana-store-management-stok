<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockInRequest;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Repositories\Contracts\StockInRepositoryInterface;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Stok Masuk untuk peran Admin.
 *
 * Menyediakan tampilan riwayat, form pencatatan, dan detail stok masuk.
 */
class StockInController extends Controller
{
    public function __construct(
        private readonly StockService               $stockService,
        private readonly StockInRepositoryInterface $stockInRepository,
    ) {}

    /**
     * Tampilkan riwayat stok masuk dengan filter produk, pemasok, dan rentang tanggal.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StockIn::class);

        $productId  = $request->query('product_id') ? (int) $request->query('product_id') : null;
        $supplierId = $request->query('supplier_id') ? (int) $request->query('supplier_id') : null;
        $startDate  = $request->query('start_date') ?: null;
        $endDate    = $request->query('end_date') ?: null;

        $stockIns = $this->stockInRepository
            ->filter($productId, $supplierId, $startDate, $endDate)
            ->with(['product', 'supplier', 'user'])
            ->latest('received_at')
            ->paginate(20)
            ->withQueryString();

        $products  = Product::orderBy('name')->get(['id', 'name', 'sku']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('admin.stock-ins.index', compact(
            'stockIns',
            'products',
            'suppliers',
            'productId',
            'supplierId',
            'startDate',
            'endDate',
        ));
    }

    /**
     * Tampilkan form pencatatan stok masuk baru.
     */
    public function create(): View
    {
        $this->authorize('create', StockIn::class);

        $products  = Product::active()->orderBy('name')->get(['id', 'name', 'sku', 'purchase_price']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('admin.stock-ins.create', compact('products', 'suppliers'));
    }

    /**
     * Simpan catatan stok masuk baru dan perbarui stok produk.
     */
    public function store(StoreStockInRequest $request): RedirectResponse
    {
        $this->authorize('create', StockIn::class);

        $stockIn = $this->stockService->recordStockIn($request->validated());

        return redirect()
            ->route('admin.stock-ins.show', $stockIn)
            ->with('success', "Stok masuk berhasil dicatat. Nomor referensi: {$stockIn->reference_number}");
    }

    /**
     * Tampilkan detail catatan stok masuk.
     */
    public function show(StockIn $stockIn): View
    {
        $this->authorize('view', $stockIn);

        $stockIn->load(['product.category', 'supplier', 'user']);

        return view('admin.stock-ins.show', compact('stockIn'));
    }
}
