<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Riwayat Stok Masuk', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            <h2 class="navbar__page-title">Riwayat Stok Masuk</h2>
            <a href="{{ route('admin.stock-ins.create') }}" class="btn btn--primary btn--sm">+ Catat Stok Masuk</a>
        </div>
    </x-slot>

    <div class="page-body">

        {{-- Filter --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__body">
                <form method="GET" action="{{ route('admin.stock-ins.index') }}" novalidate>
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
                            <label for="supplier_id" class="form-label">Pemasok</label>
                            <select id="supplier_id" name="supplier_id" class="form-select">
                                <option value="">Semua Pemasok</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                            @selected((int) $supplierId === $supplier->id)>
                                        {{ $supplier->name }}
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
                        <a href="{{ route('admin.stock-ins.index') }}" class="btn btn--secondary btn--sm">Reset</a>
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
                            <th>No. Referensi</th>
                            <th>Produk</th>
                            <th>Pemasok</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-right">Total Nilai</th>
                            <th>Tgl. Penerimaan</th>
                            <th>Dicatat Oleh</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stockIns as $stockIn)
                            <tr>
                                <td style="font-family:monospace;">{{ $stockIn->reference_number }}</td>
                                <td>
                                    <div style="font-weight:500;">{{ $stockIn->product->name ?? '-' }}</div>
                                    <div style="font-size:0.75rem; color:var(--color-text-muted);">{{ $stockIn->product->sku ?? '' }}</div>
                                </td>
                                <td>{{ $stockIn->supplier->name ?? '-' }}</td>
                                <td class="text-right">{{ number_format($stockIn->quantity) }}</td>
                                <td class="text-right">Rp {{ number_format((float) $stockIn->total_value, 0, ',', '.') }}</td>
                                <td>{{ $stockIn->received_at->translatedFormat('d M Y') }}</td>
                                <td>{{ $stockIn->user->name ?? '-' }}</td>
                                <td class="text-right">
                                    <div class="table__actions">
                                        <a href="{{ route('admin.stock-ins.show', $stockIn) }}" class="btn-link">Detail</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="table__empty">
                                <td colspan="8">
                                    Belum ada catatan stok masuk.
                                    @if ($productId || $supplierId || $startDate || $endDate)
                                        <a href="{{ route('admin.stock-ins.index') }}" class="btn-link">Reset filter</a>
                                    @else
                                        <a href="{{ route('admin.stock-ins.create') }}" class="btn-link">Catat sekarang</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($stockIns->hasPages())
            <div style="margin-top:1rem;">
                {{ $stockIns->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
