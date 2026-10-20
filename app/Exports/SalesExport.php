<?php

declare(strict_types=1);

namespace App\Exports;

use App\Services\ReportService;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected ReportService $reportService;

    public function __construct(
        protected string $startDate,
        protected string $endDate,
        ?ReportService $reportService = null,
        protected ?int $cashierId = null,
    ) {
        $this->reportService = $reportService ?? app(ReportService::class);
    }

    public function collection()
    {
        $transactions = $this->reportService->getSalesTransactions(
            $this->startDate,
            $this->endDate,
            $this->cashierId
        );

        if ($transactions->isNotEmpty()) {
            $totalAmount = (float) $transactions->sum('total_amount');
            $totalCogs   = (float) $transactions->sum('total_cogs');
            $totalProfit = (float) $transactions->sum('profit');
            $totalItems  = (int) $transactions->sum('total_items');
            $margin      = $totalAmount > 0 ? round(($totalProfit / $totalAmount) * 100, 2) : 0.0;

            // Baris ringkasan total di baris paling bawah
            $summaryRow = (object) [
                'invoice_number' => 'TOTAL KESELURUHAN',
                'sold_at'        => '',
                'cashier_name'   => '',
                'total_items'    => $totalItems,
                'total_amount'   => $totalAmount,
                'total_cogs'     => $totalCogs,
                'profit'         => $totalProfit,
                'profit_margin'  => $margin,
                'is_summary'     => true,
            ];

            return $transactions->push($summaryRow);
        }

        return $transactions;
    }

    public function headings(): array
    {
        return [
            'No. Faktur',
            'Tanggal & Waktu',
            'Kasir',
            'Total Item Terjual',
            'Total Penjualan (Rp)',
            'Total Modal / HPP (Rp)',
            'Keuntungan Bersih (Rp)',
            'Margin (%)',
        ];
    }

    /**
     * @param  object $row
     */
    public function map($row): array
    {
        return [
            $row->invoice_number,
            !empty($row->sold_at) ? Carbon::parse($row->sold_at)->translatedFormat('d/m/Y H:i') : '-',
            $row->cashier_name ?: '-',
            number_format((float) $row->total_items),
            number_format((float) $row->total_amount, 2, ',', '.'),
            number_format((float) $row->total_cogs, 2, ',', '.'),
            number_format((float) $row->profit, 2, ',', '.'),
            number_format((float) $row->profit_margin, 2, ',', '.') . '%',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
