<?php

namespace App\Http\Controllers\Kasir;

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
 * Controller Penjualan untuk peran Kasir.
 *
 * Menyediakan tampilan daftar, form pembuatan, detail transaksi,
 * dan unduhan invoice PDF.
 */
class SaleController extends Controller
{
    public function __construct(
        private readonly SaleService               $saleService,
        private readonly SaleRepositoryInterface   $saleRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Index — daftar riwayat transaksi kasir ini
    // -------------------------------------------------------------------------

    /**
     * Tampilkan daftar transaksi penjualan milik kasir yang sedang login.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Sale::class);

        $startDate = $request->query('start_date') ?: null;
        $endDate   = $request->query('end_date') ?: null;

        // Kasir hanya melihat transaksi miliknya sendiri
        $sales = $this->saleRepository
            ->filterByDateAndCashier($startDate, $endDate, auth()->id())
            ->with(['user'])
            ->latest('sold_at')
            ->paginate(100)
            ->withQueryString();

        return view('kasir.sales.index', compact('sales', 'startDate', 'endDate'));
    }

    // -------------------------------------------------------------------------
    // Create — form transaksi baru
    // -------------------------------------------------------------------------

    /**
     * Tampilkan form pembuatan transaksi penjualan baru.
     */
    public function create(): View
    {
        $this->authorize('create', Sale::class);

        // Produk aktif untuk ditampilkan di form (Alpine.js akan handle search)
        $products = Product::active()
            ->with('category')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'selling_price', 'current_stock', 'unit', 'category_id']);

        return view('kasir.sales.create', compact('products'));
    }

    // -------------------------------------------------------------------------
    // Store — simpan transaksi
    // -------------------------------------------------------------------------

    /**
     * Simpan transaksi penjualan baru secara atomik.
     */
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
            ->route('kasir.sales.show', $sale)
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

        return view('kasir.sales.show', compact('sale'));
    }

    // -------------------------------------------------------------------------
    // Download Invoice — unduh invoice PDF
    // -------------------------------------------------------------------------

    /**
     * Hasilkan dan unduh invoice transaksi dalam format PDF.
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
