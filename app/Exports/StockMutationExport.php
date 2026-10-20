<?php

declare(strict_types=1);

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockMutationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ReportService $reportService,
    ) {}

    public function collection()
    {
        return $this->reportService->getStockMutationReport($this->startDate, $this->endDate);
    }

    public function headings(): array
    {
        return [
            'Produk',
            'SKU',
            'Kategori',
            'Stok Masuk',
            'Stok Keluar',
            'Penyesuaian',
            'Perubahan Bersih',
        ];
    }

    /**
     * @param  object $row
     */
    public function map($row): array
    {
        return [
            $row->product_name,
            $row->sku,
            $row->category_name,
            $row->qty_in,
            $row->qty_out,
            $row->adjustments,
            $row->net_change,
        ];
    }
}
