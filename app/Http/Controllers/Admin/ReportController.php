<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exports\InventoryExport;
use App\Exports\SalesExport;
use App\Exports\StockMutationExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Controller untuk modul laporan dan ekspor data.
 *
 * Hanya dapat diakses oleh Admin (dijaga oleh middleware role:admin).
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    /**
     * Tampilkan laporan nilai inventaris per kategori.
     */
    public function inventory(): View
    {
        $data       = $this->reportService->getInventoryValueByCategory();
        $grandTotal = $data->sum('total_value');

        return view('admin.reports.inventory', compact('data', 'grandTotal'));
    }

    /**
     * Tampilkan laporan penjualan dan keuntungan berdasarkan rentang tanggal dan kasir.
     */
    public function sales(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', now()->toDateString());
        $cashierId = $request->filled('cashier_id') ? (int) $request->get('cashier_id') : null;

        $report   = $this->reportService->getSalesReport($startDate, $endDate, $cashierId);
        $cashiers = User::whereIn('role', ['admin', 'kasir'])->orderBy('name')->get(['id', 'name']);

        return view('admin.reports.sales', compact('report', 'startDate', 'endDate', 'cashierId', 'cashiers'));
    }

    /**
     * Tampilkan laporan mutasi stok berdasarkan rentang tanggal.
     */
    public function stockMutation(Request $request): View
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', now()->toDateString());

        $mutations = $this->reportService->getStockMutationReport($startDate, $endDate);

        return view('admin.reports.stock-mutation', compact('mutations', 'startDate', 'endDate'));
    }

    /**
     * Ekspor laporan ke file Excel (.xlsx).
     *
     * @param  string $type  Jenis laporan: inventory | sales | stock-mutation
     */
    public function export(Request $request, string $type): BinaryFileResponse
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', now()->toDateString());
        $cashierId = $request->filled('cashier_id') ? (int) $request->get('cashier_id') : null;

        return match ($type) {
            'inventory' => Excel::download(
                new InventoryExport($this->reportService),
                'inventaris-' . now()->format('Ymd') . '.xlsx',
            ),
            'sales' => Excel::download(
                new SalesExport($startDate, $endDate, $this->reportService, $cashierId),
                'penjualan-' . $startDate . '-' . $endDate . '.xlsx',
            ),
            'stock-mutation' => Excel::download(
                new StockMutationExport($startDate, $endDate, $this->reportService),
                'mutasi-stok-' . $startDate . '-' . $endDate . '.xlsx',
            ),
            default => abort(404),
        };
    }
}
