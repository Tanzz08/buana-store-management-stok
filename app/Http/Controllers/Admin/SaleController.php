<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Repositories\Contracts\SaleRepositoryInterface;
use App\Services\SaleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Controller Penjualan untuk peran Admin (hanya baca).
 *
 * Admin dapat melihat semua transaksi penjualan dan mengunduh invoice,
 * tetapi tidak dapat membuat transaksi baru.
 */
class SaleController extends Controller
{
    public function __construct(
    private readonly SaleRepositoryInterface $saleRepository,
    private readonly SaleService $saleService // <-- Tambahkan ini
    ) {}

    // -------------------------------------------------------------------------
    // Index — daftar semua transaksi penjualan
    // -------------------------------------------------------------------------

    /**
     * Tampilkan daftar seluruh transaksi penjualan dengan filter tanggal & kasir.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Sale::class);

        $startDate = $request->query('start_date') ?: null;
        $endDate   = $request->query('end_date') ?: null;
        $cashierId = $request->query('cashier_id') ? (int) $request->query('cashier_id') : null;

        $sales = $this->saleRepository
            ->filterByDateAndCashier($startDate, $endDate, $cashierId)
            ->with(['user'])
            ->latest('sold_at')
            ->paginate(100)
            ->withQueryString();

        $cashiers = User::where('role', 'kasir')->orderBy('name')->get(['id', 'name']);

        return view('admin.sales.index', compact('sales', 'cashiers', 'startDate', 'endDate', 'cashierId'));
    }

    public function create(): View
    {
        $this->authorize('create', Sale::class);

        // Produk aktif untuk ditampilkan di form (Alpine.js akan handle search)
        $products = Product::active()
            ->with('category')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'selling_price', 'current_stock', 'unit', 'category_id']);

        return view('admin.sales.create', compact('products'));
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $this->authorize('create', Sale::class);

        try {
            $sale = $this->saleService->createSale(
                data: [],
                items: $request->validated()['items'],
            );
        } catch (InsufficientStockException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan transaksi. Silakan coba lagi.');
        }

        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('success', "Transaksi berhasil disimpan. Nomor faktur: {$sale->invoice_number}");
    }

    // -------------------------------------------------------------------------
    // Show — detail transaksi
    // -------------------------------------------------------------------------

    /**
     * Tampilkan detail transaksi penjualan.
     */
    public function show(Sale $sale): View
    {
        $this->authorize('view', $sale);

        $sale->load(['items.product', 'user']);

        return view('admin.sales.show', compact('sale'));
    }

    // -------------------------------------------------------------------------
    // Download Invoice
    // -------------------------------------------------------------------------

    /**
     * Unduh invoice transaksi dalam format PDF.
     */
    public function downloadInvoice(Sale $sale): Response
    {
        $this->authorize('downloadInvoice', $sale);

        $sale->load(['items.product', 'user']);

        $pdf = Pdf::loadView('invoices.sale', compact('sale'))
            ->setPaper('a5', 'portrait');

        $filename = "Invoice-{$sale->invoice_number}.pdf";

        return $pdf->download($filename);
    }
}
