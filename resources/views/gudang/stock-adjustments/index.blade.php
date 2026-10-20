<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Riwayat Penyesuaian Stok', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            <h2 class="navbar__page-title">Riwayat Penyesuaian Stok</h2>
            <a href="{{ route('gudang.stock-adjustments.create') }}" class="btn btn--primary btn--sm">+ Catat Penyesuaian Stok</a>
        </div>
    </x-slot>

    <div class="page-body">

        {{-- Filter --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__body">
                <form method="GET" action="{{ route('gudang.stock-adjustments.index') }}" novalidate>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">

                        <div class="form-group" style="margin-bottom:0;">
                            <label for="product_id" class="form-label">Produk</label>
                            <select id="product_id" name="product_id" class="form-select">
                                <option value="">Semua Produk</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                            @selected((int) $productId === $product->id)>
                                        {{ $product->name }} ({{ $product->sku }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

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
                        <a href="{{ route('gudang.stock-adjustments.index') }}" class="btn btn--secondary btn--sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrapper">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-right">Stok Sebelum</th>
                            <th class="text-right">Stok Sesudah</th>
                            <th class="text-right">Selisih</th>
                            <th>Alasan</th>
                            <th>Tanggal</th>
                            <th>Dicatat Oleh</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adjustments as $adjustment)
                            <tr>
                                <td>
                                    <div style="font-weight:500;">{{ $adjustment->product->name ?? '-' }}</div>
                                    <div style="font-size:0.75rem; color:var(--color-text-muted);">{{ $adjustment->product->sku ?? '' }}</div>
                                </td>
                                <td class="text-right">{{ number_format($adjustment->stock_before) }}</td>
                                <td class="text-right">{{ number_format($adjustment->stock_after) }}</td>
                                <td class="text-right" style="font-weight:600; @if($adjustment->difference > 0) color:var(--color-success); @elseif($adjustment->difference < 0) color:var(--color-error); @else color:var(--color-text-muted); @endif">
                                    {{ $adjustment->difference > 0 ? '+' : '' }}{{ number_format($adjustment->difference) }}
                                </td>
                                <td style="max-width:12rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $adjustment->reason }}</td>
                                <td>{{ $adjustment->adjusted_at->translatedFormat('d M Y, H:i') }}</td>
                                <td>{{ $adjustment->user->name ?? '-' }}</td>
                                <td class="text-right">
                                    <div class="table__actions">
                                        <a href="{{ route('gudang.stock-adjustments.show', $adjustment) }}" class="btn-link">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="table__empty">
                                <td colspan="8">
                                    Belum ada catatan penyesuaian stok.
                                    @if ($productId || $startDate || $endDate)
                                        <a href="{{ route('gudang.stock-adjustments.index') }}" class="btn-link">Reset filter</a>
                                    @else
                                        <a href="{{ route('gudang.stock-adjustments.create') }}" class="btn-link">Catat sekarang</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($adjustments->hasPages())
            <div style="margin-top:1rem;">
                {{ $adjustments->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
