<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Laporan', 'url' => route('admin.reports.inventory')],
            ['label' => 'Mutasi Stok', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Laporan Mutasi Stok</h2>
    </x-slot>

    <div class="page-body">
        <div class="space-y-6">

            {{-- Navigasi Tab Laporan --}}
            <x-report-nav-tabs />

            {{-- Filter Tanggal --}}
            <div class="card">
                <div class="card__body">
                    <form method="GET" action="{{ route('admin.reports.stock-mutation') }}">
                        <div class="grid grid--3">
                            <div class="form-group" style="margin-bottom:0">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date"
                                       id="start_date"
                                       name="start_date"
                                       value="{{ $startDate }}"
                                       class="form-input">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date"
                                       id="end_date"
                                       name="end_date"
                                       value="{{ $endDate }}"
                                       class="form-input">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="btn btn--primary w-full">
                                    Tampilkan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabel Mutasi + Ekspor --}}
            <div class="table-wrapper">
                <div class="card__header flex items-center justify-between">
                    <span>Detail Mutasi Stok</span>
                    <a href="{{ route('admin.reports.export', ['type' => 'stock-mutation', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                       class="btn btn--success btn--sm">
                        Ekspor Excel
                    </a>
                </div>
                <div class="table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>SKU</th>
                                <th>Kategori</th>
                                <th class="text-center">Stok Masuk</th>
                                <th class="text-center">Stok Keluar</th>
                                <th class="text-center">Penyesuaian</th>
                                <th class="text-center">Perubahan Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mutations as $row)
                                <tr>
                                    <td class="font-semibold">{{ $row->product_name }}</td>
                                    <td class="font-mono">{{ $row->sku }}</td>
                                    <td>{{ $row->category_name }}</td>
                                    <td class="text-center">{{ number_format($row->qty_in) }}</td>
                                    <td class="text-center">{{ number_format($row->qty_out) }}</td>
                                    <td class="text-center">{{ number_format($row->adjustments) }}</td>
                                    <td class="text-center font-semibold
                                        @if ($row->net_change > 0) text-green-600
                                        @elseif ($row->net_change < 0) text-red-600
                                        @else text-gray-500
                                        @endif">
                                        {{ $row->net_change > 0 ? '+' : '' }}{{ number_format($row->net_change) }}
                                    </td>
                                </tr>
                            @empty
                                <tr class="table__empty">
                                    <td colspan="7">Tidak ada mutasi stok pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
