<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Laporan', 'url' => route('admin.reports.inventory')],
            ['label' => 'Nilai Inventaris', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="navbar__page-title">Laporan Nilai Inventaris</h2>
            <a href="{{ route('admin.reports.export', 'inventory') }}" class="btn btn--success btn--sm">
                Ekspor Excel
            </a>
        </div>
    </x-slot>

    <div class="page-body">
        <x-report-nav-tabs />

        <div class="table-wrapper">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th class="text-center">Jumlah Produk</th>
                            <th class="text-right">Total Nilai Inventaris</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $row)
                            <tr>
                                <td class="font-semibold">{{ $row->category_name }}</td>
                                <td class="text-center">{{ number_format($row->product_count) }}</td>
                                <td class="text-right">Rp {{ number_format((float) $row->total_value, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr class="table__empty">
                                <td colspan="3">Belum ada data inventaris.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($data->isNotEmpty())
                        <tfoot>
                            <tr style="background-color:var(--gray-50);border-top:2px solid var(--gray-200)">
                                <td colspan="2" class="text-right font-bold" style="padding:var(--space-3) var(--space-4)">
                                    Total Keseluruhan
                                </td>
                                <td class="text-right font-bold" style="padding:var(--space-3) var(--space-4)">
                                    Rp {{ number_format((float) $grandTotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
