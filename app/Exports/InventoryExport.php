<?php

declare(strict_types=1);

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    public function collection()
    {
        return $this->reportService->getInventoryValueByCategory();
    }

    public function headings(): array
    {
        return [
            'Kategori',
            'Jumlah Produk',
            'Total Nilai Inventaris (Rp)',
        ];
    }

    /**
     * @param  object $row
     */
    public function map($row): array
    {
        return [
            $row->category_name,
            $row->product_count,
            number_format((float) $row->total_value, 2, ',', '.'),
        ];
    }
}
