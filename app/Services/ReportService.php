<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use App\Models\StockIn;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service laporan dan analitik inventaris Buana Store.
 *
 * Menyediakan data untuk:
 * - Laporan nilai inventaris per kategori
 * - Laporan penjualan berdasarkan rentang tanggal
 * - Laporan mutasi stok berdasarkan rentang tanggal
 * - Ringkasan dasbor (summary cards)
 */
class ReportService
{
    /**
     * Hitung total nilai inventaris produk aktif, dikelompokkan per kategori.
     *
     * @return Collection<int, object{category_name: string, product_count: int, total_value: float}>
     */
    public function getInventoryValueByCategory(): Collection
    {
        return DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.status', 'active')
            ->groupBy('categories.id', 'categories.name')
            ->select([
                'categories.name as category_name',
                DB::raw('COUNT(products.id) as product_count'),
                DB::raw('SUM(products.current_stock * products.purchase_price) as total_value'),
            ])
            ->orderBy('categories.name')
            ->get();
    }

    /**
     * Buat laporan penjualan komprehensif dalam rentang tanggal tertentu,
     * mencakup omset penjualan, total HPP/modal, keuntungan bersih,
     * margin laba, rata-rata transaksi, tren grafik harian, serta performa produk.
     *
     * @param  string   $startDate  Format: Y-m-d
     * @param  string   $endDate    Format: Y-m-d
     * @param  int|null $cashierId  Filter kasir tertentu (opsional)
     * @return array{
     *     total_amount: float,
     *     total_revenue: float,
     *     total_cogs: float,
     *     total_profit: float,
     *     profit_margin: float,
     *     transaction_count: int,
     *     total_items_sold: int,
     *     average_order_value: float,
     *     chart_data: array{labels: list<string>, revenues: list<float>, profits: list<float>},
     *     top_products: Collection,
     *     transactions: Collection
     * }
     */
    public function getSalesReport(string $startDate, string $endDate, ?int $cashierId = null): array
    {
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime   = $endDate . ' 23:59:59';

        // 1. Query dasar item penjualan
        $itemsTotalsQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sold_at', [$startDateTime, $endDateTime]);

        if ($cashierId !== null) {
            $itemsTotalsQuery->where('sales.user_id', $cashierId);
        }

        // Hitung total pendapatan, total modal (HPP), dan total kuantitas barang terjual
        $totals = (clone $itemsTotalsQuery)->select([
            DB::raw('COALESCE(SUM(sale_items.subtotal), 0) as total_revenue'),
            DB::raw('COALESCE(SUM(sale_items.quantity * products.purchase_price), 0) as total_cogs'),
            DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_items_sold'),
        ])->first();

        // 2. Query jumlah transaksi
        $salesBaseQuery = DB::table('sales')
            ->whereBetween('sold_at', [$startDateTime, $endDateTime]);
        if ($cashierId !== null) {
            $salesBaseQuery->where('user_id', $cashierId);
        }
        $transactionCount = $salesBaseQuery->count();

        $totalRevenue      = (float) ($totals->total_revenue ?? 0);
        $totalCogs         = (float) ($totals->total_cogs ?? 0);
        $totalProfit       = round($totalRevenue - $totalCogs, 2);
        $totalItemsSold    = (int) ($totals->total_items_sold ?? 0);
        $profitMargin      = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0.0;
        $averageOrderValue = $transactionCount > 0 ? round($totalRevenue / $transactionCount, 2) : 0.0;

        // 3. Top 10 Produk beserta Penjualan, HPP, Keuntungan & Margin
        $topProducts = (clone $itemsTotalsQuery)
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('sale_items.product_id', 'products.name', 'products.sku', 'categories.name')
            ->select([
                'products.name as product_name',
                'products.sku',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as total_cogs'),
                DB::raw('SUM(sale_items.subtotal) - SUM(sale_items.quantity * products.purchase_price) as total_profit'),
            ])
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $rev  = (float) $item->total_revenue;
                $prof = (float) $item->total_profit;
                $item->profit_margin = $rev > 0 ? round(($prof / $rev) * 100, 2) : 0.0;
                return $item;
            });

        // 4. Data Tren Harian untuk Grafik Monitoring (Chart.js)
        $dailyRows = (clone $itemsTotalsQuery)
            ->groupBy(DB::raw('DATE(sales.sold_at)'))
            ->select([
                DB::raw('DATE(sales.sold_at) as sale_date'),
                DB::raw('SUM(sale_items.subtotal) as revenue'),
                DB::raw('SUM(sale_items.quantity * products.purchase_price) as cogs'),
                DB::raw('SUM(sale_items.subtotal) - SUM(sale_items.quantity * products.purchase_price) as profit'),
            ])
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $chartLabels   = [];
        $chartRevenues = [];
        $chartProfits  = [];

        $startCarbon = Carbon::parse($startDate);
        $endCarbon   = Carbon::parse($endDate);
        $diffDays    = $startCarbon->diffInDays($endCarbon);

        if ($diffDays <= 90) {
            $cursor = $startCarbon->copy();
            while ($cursor->lte($endCarbon)) {
                $dateKey         = $cursor->toDateString();
                $chartLabels[]   = $cursor->translatedFormat('d M');
                $row             = $dailyRows->get($dateKey);
                $chartRevenues[] = $row ? (float) $row->revenue : 0.0;
                $chartProfits[]  = $row ? (float) $row->profit : 0.0;
                $cursor->addDay();
            }
        } else {
            foreach ($dailyRows as $dateKey => $row) {
                $chartLabels[]   = Carbon::parse($dateKey)->translatedFormat('d M Y');
                $chartRevenues[] = (float) $row->revenue;
                $chartProfits[]  = (float) $row->profit;
            }
        }

        // 5. Rincian Riwayat Transaksi
        $transactions = $this->getSalesTransactions($startDate, $endDate, $cashierId);

        return [
            'total_amount'        => $totalRevenue,
            'total_revenue'       => $totalRevenue,
            'total_cogs'          => $totalCogs,
            'total_profit'        => $totalProfit,
            'profit_margin'       => $profitMargin,
            'transaction_count'   => $transactionCount,
            'total_items_sold'    => $totalItemsSold,
            'average_order_value' => $averageOrderValue,
            'chart_data'          => [
                'labels'   => $chartLabels,
                'revenues' => $chartRevenues,
                'profits'  => $chartProfits,
            ],
            'top_products'        => $topProducts,
            'transactions'        => $transactions,
        ];
    }

    /**
     * Dapatkan daftar rincian transaksi penjualan beserta HPP dan keuntungan per transaksi.
     *
     * @param  string   $startDate  Format: Y-m-d
     * @param  string   $endDate    Format: Y-m-d
     * @param  int|null $cashierId  Filter kasir tertentu (opsional)
     * @return Collection
     */
    public function getSalesTransactions(string $startDate, string $endDate, ?int $cashierId = null): Collection
    {
        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime   = $endDate . ' 23:59:59';

        $query = DB::table('sales')
            ->join('users', 'sales.user_id', '=', 'users.id')
            ->leftJoin('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.sold_at', [$startDateTime, $endDateTime]);

        if ($cashierId !== null) {
            $query->where('sales.user_id', $cashierId);
        }

        return $query
            ->groupBy('sales.id', 'sales.invoice_number', 'sales.sold_at', 'sales.total_amount', 'users.name')
            ->select([
                'sales.id',
                'sales.invoice_number',
                'sales.sold_at',
                'sales.total_amount',
                'users.name as cashier_name',
                DB::raw('COALESCE(SUM(sale_items.quantity), 0) as total_items'),
                DB::raw('COALESCE(SUM(sale_items.quantity * products.purchase_price), 0) as total_cogs'),
                DB::raw('sales.total_amount - COALESCE(SUM(sale_items.quantity * products.purchase_price), 0) as profit'),
            ])
            ->orderByDesc('sales.sold_at')
            ->get()
            ->map(function ($item) {
                $total  = (float) $item->total_amount;
                $profit = (float) $item->profit;
                $item->profit_margin = $total > 0 ? round(($profit / $total) * 100, 2) : 0.0;
                return $item;
            });
    }

    /**
     * Laporan mutasi stok (stok masuk, keluar, penyesuaian) per produk
     * dalam rentang tanggal tertentu.
     *
     * Hanya produk yang memiliki setidaknya satu mutasi dalam rentang
     * tanggal yang disertakan dalam hasil.
     *
     * @param  string $startDate  Format: Y-m-d
     * @param  string $endDate    Format: Y-m-d
     * @return Collection
     */
    public function getStockMutationReport(string $startDate, string $endDate): Collection
    {
        $start = $startDate . ' 00:00:00';
        $end   = $endDate . ' 23:59:59';

        // Subquery: qty masuk per produk dalam rentang
        $qtyIn = DB::table('stock_ins')
            ->select('product_id', DB::raw('SUM(quantity) as qty_in'))
            ->whereBetween('received_at', [$startDate, $endDate])
            ->groupBy('product_id');

        // Subquery: qty keluar (penjualan) per produk dalam rentang
        $qtyOut = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.quantity) as qty_out'))
            ->whereBetween('sales.sold_at', [$start, $end])
            ->groupBy('sale_items.product_id');

        // Subquery: total penyesuaian per produk dalam rentang
        $adjustments = DB::table('stock_adjustments')
            ->select('product_id', DB::raw('SUM(difference) as adjustments'))
            ->whereBetween('adjusted_at', [$start, $end])
            ->groupBy('product_id');

        return DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoinSub($qtyIn, 'si', 'products.id', '=', 'si.product_id')
            ->leftJoinSub($qtyOut, 'so', 'products.id', '=', 'so.product_id')
            ->leftJoinSub($adjustments, 'adj', 'products.id', '=', 'adj.product_id')
            ->where(function ($q) {
                // Hanya produk yang punya mutasi di periode ini
                $q->whereNotNull('si.qty_in')
                  ->orWhereNotNull('so.qty_out')
                  ->orWhereNotNull('adj.adjustments');
            })
            ->select([
                'products.name as product_name',
                'products.sku',
                'categories.name as category_name',
                DB::raw('COALESCE(si.qty_in, 0) as qty_in'),
                DB::raw('COALESCE(so.qty_out, 0) as qty_out'),
                DB::raw('COALESCE(adj.adjustments, 0) as adjustments'),
                DB::raw('COALESCE(si.qty_in, 0) - COALESCE(so.qty_out, 0) + COALESCE(adj.adjustments, 0) as net_change'),
            ])
            ->orderBy('products.name')
            ->get();
    }

    /**
     * Ringkasan statistik untuk kartu summary dasbor admin.
     *
     * @return array{
     *     total_active_products: int,
     *     total_inventory_value: float,
     *     today_transactions: int,
     *     low_stock_count: int
     * }
     */
    public function getDashboardSummary(): array
    {
        return [
            'total_active_products' => Product::where('status', 'active')->count(),
            'total_inventory_value' => (float) Product::where('status', 'active')
                ->sum(DB::raw('current_stock * purchase_price')),
            'today_transactions'    => Sale::whereDate('sold_at', today())->count(),
            'low_stock_count'       => Product::where('status', 'active')
                ->whereColumn('current_stock', '<=', 'minimum_stock')
                ->count(),
        ];
    }
}
