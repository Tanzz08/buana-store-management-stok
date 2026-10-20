<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Riwayat Penjualan', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            <h2 class="navbar__page-title">Riwayat Penjualan</h2>
            <a href="{{ route('kasir.sales.create') }}" class="btn btn--primary btn--sm">+ Transaksi Baru</a>
        </div>
    </x-slot>

    <div class="page-body">

        {{-- Filter --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__body">
                <form method="GET" action="{{ route('kasir.sales.index') }}" novalidate>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input id="start_date" type="date" name="start_date"
                                   value="{{ $startDate }}" class="form-input">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input id="end_date" type="date" name="end_date"
                                   value="{{ $endDate }}" class="form-input">
                        </div>
                    </div>
                    <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                        <button type="submit" class="btn btn--primary btn--sm">Terapkan Filter</button>
                        <a href="{{ route('kasir.sales.index') }}" class="btn btn--secondary btn--sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel Penjualan --}}
        <div class="table-wrapper">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. Faktur</th>
                            <th class="text-right">Total</th>
                            <th>Waktu Transaksi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td style="font-family:monospace;">{{ $sale->invoice_number }}</td>
                                <td class="text-right" style="font-weight:500;">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</td>
                                <td>{{ $sale->sold_at->translatedFormat('d M Y, H:i') }}</td>
                                <td class="text-right">
                                    <div class="table__actions">
                                        <a href="{{ route('kasir.sales.show', $sale) }}" class="btn-link">Detail</a>
                                        <a href="{{ route('kasir.sales.invoice', $sale) }}" class="btn-link--success">Invoice</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="table__empty">
                                <td colspan="4">
                                    Belum ada transaksi penjualan.
                                    @if ($startDate || $endDate)
                                        <a href="{{ route('kasir.sales.index') }}" class="btn-link">Reset filter</a>
                                    @else
                                        <a href="{{ route('kasir.sales.create') }}" class="btn-link">Buat transaksi baru</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($sales->hasPages())
            <div style="margin-top:1rem;">
                {{ $sales->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
