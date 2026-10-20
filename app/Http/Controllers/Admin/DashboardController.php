<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controller untuk dasbor admin.
 *
 * Menampilkan ringkasan statistik dan tren penjualan 30 hari terakhir.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    /**
     * Tampilkan dasbor admin dengan summary cards dan chart tren penjualan.
     */
    public function index(): View
    {
        $summary = $this->reportService->getDashboardSummary();

        // Tren penjualan 30 hari terakhir
        $salesTrend = Sale::select(
            DB::raw('DATE(sold_at) as date'),
            DB::raw('SUM(total_amount) as total'),
        )
        ->where('sold_at', '>=', now()->subDays(29)->startOfDay())
        ->groupBy(DB::raw('DATE(sold_at)'))
        ->orderBy('date')
        ->get()
        ->keyBy('date');

        $labels = [];
        $values = [];

        for ($i = 29; $i >= 0; $i--) {
            $date     = now()->subDays($i)->toDateString();
            $labels[] = now()->subDays($i)->format('d M');
            $values[] = $salesTrend->has($date) ? (float) $salesTrend[$date]->total : 0;
        }

        $chartData = json_encode(['labels' => $labels, 'values' => $values]);

        return view('admin.dashboard', compact('summary', 'chartData'));
    }
}
