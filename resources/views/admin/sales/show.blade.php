<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Riwayat Penjualan', 'url' => route('admin.sales.index')],
            ['label' => 'Detail Transaksi', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Detail Transaksi Penjualan</h2>
    </x-slot>

    <div class="page-body">
        <div class="card">
            <div class="card__header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:0.75rem;">
                <div>
                    <h3 style="font-weight:600; margin:0;">Detail Transaksi Penjualan</h3>
                    <p style="font-size:0.875rem; color:var(--color-text-muted); margin:0.25rem 0 0; font-family:monospace;">{{ $sale->invoice_number }}</p>
                </div>
                <div style="display:flex; gap:0.5rem; align-items:center;">
                    <a href="{{ route('admin.sales.invoice', $sale) }}" class="btn btn--success btn--sm">
                        <svg style="width:1rem;height:1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh Invoice PDF
                    </a>
                    <a href="{{ route('admin.sales.index') }}" class="btn btn--secondary btn--sm">&larr; Kembali</a>
                </div>
            </div>

            {{-- Info Transaksi --}}
            <div class="card__body" style="padding:0; border-bottom:1px solid var(--color-border);">
                <dl>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Nomor Faktur</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text); font-family:monospace;">{{ $sale->invoice_number }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Kasir</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $sale->user->name ?? '-' }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem;">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Waktu Transaksi</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $sale->sold_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                    </div>
                </dl>
            </div>

            {{-- Tabel Item --}}
            <div class="card__body">
                <h4 style="font-size:0.875rem; font-weight:600; color:var(--color-text); margin-bottom:0.75rem;">Daftar Produk Terjual</h4>
                <div class="table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Produk</th>
                                <th class="text-right">Harga Jual</th>
                                <th class="text-right">Jumlah</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $i => $item)
                                <tr>
                                    <td style="color:var(--color-text-muted);">{{ $i + 1 }}</td>
                                    <td>
                                        <div style="font-weight:500;">{{ $item->product->name ?? '-' }}</div>
                                        <div style="font-size:0.75rem; color:var(--color-text-muted);">{{ $item->product->sku ?? '' }}</div>
                                    </td>
                                    <td class="text-right">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($item->quantity) }}</td>
                                    <td class="text-right" style="font-weight:600;">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right" style="font-weight:700;">Total:</td>
                                <td class="text-right" style="font-weight:700; font-size:1rem;">Rp {{ number_format((float) $sale->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
