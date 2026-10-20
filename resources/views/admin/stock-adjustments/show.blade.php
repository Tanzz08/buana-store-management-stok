<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Riwayat Penyesuaian Stok', 'url' => route('admin.stock-adjustments.index')],
            ['label' => 'Detail Penyesuaian Stok', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Detail Penyesuaian Stok</h2>
    </x-slot>

    <div class="page-body">
        <x-flash-message />

        <div class="card">
            <div class="card__header" style="display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h3 style="font-weight:600; margin:0;">Detail Penyesuaian Stok</h3>
                    <p style="font-size:0.875rem; color:var(--color-text-muted); margin:0.25rem 0 0;">
                        {{ $stockAdjustment->adjusted_at->translatedFormat('d F Y, H:i') }} WIB
                    </p>
                </div>
                <div style="display:flex; align-items:center; gap:0.5rem;">
                    @can('update', $stockAdjustment)
                        <a href="{{ route('admin.stock-adjustments.edit', $stockAdjustment) }}" class="btn btn--primary btn--sm">Edit</a>
                    @endcan
                    @can('delete', $stockAdjustment)
                        <form method="POST"
                              action="{{ route('admin.stock-adjustments.destroy', $stockAdjustment) }}"
                              class="inline"
                              x-data
                              @submit.prevent="
                                if (confirm('Hapus penyesuaian stok ini? Tindakan ini akan mengembalikan stok produk.')) {
                                    $el.submit();
                                }
                              ">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn--danger btn--sm">
                                Hapus
                            </button>
                        </form>
                    @endcan
                    <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn--secondary btn--sm">&larr; Kembali</a>
                </div>
            </div>
            <div class="card__body" style="padding:0;">
                <dl>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Produk</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">
                            {{ $stockAdjustment->product->name ?? '-' }}
                            @if ($stockAdjustment->product)
                                <span style="color:var(--color-text-muted);">({{ $stockAdjustment->product->sku }})</span>
                                @if ($stockAdjustment->product->category)
                                    <span class="badge badge--indigo" style="margin-left:0.5rem;">{{ $stockAdjustment->product->category->name }}</span>
                                @endif
                            @endif
                        </dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Stok Sebelum</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ number_format($stockAdjustment->stock_before) }} unit</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Stok Sesudah</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ number_format($stockAdjustment->stock_after) }} unit</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Selisih</dt>
                        <dd style="font-size:0.875rem; font-weight:600;
                            @if($stockAdjustment->difference > 0) color:var(--color-success);
                            @elseif($stockAdjustment->difference < 0) color:var(--color-error);
                            @else color:var(--color-text-muted);
                            @endif">
                            {{ $stockAdjustment->difference > 0 ? '+' : '' }}{{ number_format($stockAdjustment->difference) }} unit
                        </dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Alasan Penyesuaian</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $stockAdjustment->reason }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Tanggal Penyesuaian</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $stockAdjustment->adjusted_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem; border-bottom:1px solid var(--color-border);">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Dicatat Oleh</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $stockAdjustment->user->name ?? '-' }}</dd>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 2fr; gap:1rem; padding:0.875rem 1.5rem;">
                        <dt style="font-size:0.875rem; font-weight:500; color:var(--color-text-muted);">Dicatat Pada</dt>
                        <dd style="font-size:0.875rem; color:var(--color-text);">{{ $stockAdjustment->created_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-app-layout>
