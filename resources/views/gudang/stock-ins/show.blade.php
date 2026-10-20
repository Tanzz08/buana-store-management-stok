<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Riwayat Stok Masuk', 'url' => route('gudang.stock-ins.index')],
            ['label' => 'Detail Stok Masuk', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Detail Stok Masuk</h2>
    </x-slot>

    <div class="page-body">
        <x-flash-message />

        <div class="card">
            <div class="card__header" style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="font-weight:600; margin:0;">Detail Stok Masuk</h3>
                    <p style="font-size:0.875rem; color:var(--color-text-muted); margin:0.25rem 0 0; font-family:monospace;">{{ $stockIn->reference_number }}</p>
                </div>
                <a href="{{ route('gudang.stock-ins.index') }}" class="btn btn--secondary btn--sm">&larr; Kembali</a>
            </div>
            <div class="card__body" style="padding:0;">
                <dl>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Nomor Referensi</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text); font-family:monospace;">{{ $stockIn->reference_number }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Produk</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">
                            {{ $stockIn->product->name ?? '-' }}
                            @if ($stockIn->product)
                                <span style="color:var(--color-text-muted);">({{ $stockIn->product->sku }})</span>
                                @if ($stockIn->product->category)
                                    <span class="badge badge--indigo" style="margin-left:0.5rem;">{{ $stockIn->product->category->name }}</span>
                                @endif
                            @endif
                        </dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Pemasok</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $stockIn->supplier->name ?? '-' }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Jumlah Unit</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ number_format($stockIn->quantity) }} unit</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Harga Beli Per Unit</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">Rp {{ number_format((float) $stockIn->unit_price, 2, ',', '.') }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Total Nilai</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text); font-weight:600;">Rp {{ number_format((float) $stockIn->total_value, 2, ',', '.') }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Tanggal Penerimaan</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $stockIn->received_at->translatedFormat('d F Y') }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Dicatat Oleh</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $stockIn->user->name ?? '-' }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem;">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Dicatat Pada</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $stockIn->created_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
