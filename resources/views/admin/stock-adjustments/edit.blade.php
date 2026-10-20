<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Riwayat Penyesuaian Stok', 'url' => route('admin.stock-adjustments.index')],
            ['label' => 'Edit Penyesuaian Stok', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Edit Penyesuaian Stok</h2>
    </x-slot>

    <div class="page-body">
        <div class="card">
            <div class="card__header">Form Edit Penyesuaian Stok (Stok Opname)</div>
            <div class="card__body">
                <form method="POST" action="{{ route('admin.stock-adjustments.update', $stockAdjustment) }}" novalidate
                      x-data="{ loading: false, selectedStock: {{ $stockAdjustment->product->current_stock ?? 'null' }}, products: {{ Js::from($products->map(fn($p) => ['id' => $p->id, 'current_stock' => $p->current_stock])) }} }"
                      @submit="loading = true">
                    @csrf
                    @method('PUT')

                    {{-- Produk --}}
                    <div class="form-group">
                        <label for="product_id" class="form-label">
                            Produk <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <select id="product_id" name="product_id" required
                                aria-describedby="{{ $errors->has('product_id') ? 'product_id-error' : 'product_id-hint' }}"
                                aria-invalid="{{ $errors->has('product_id') ? 'true' : 'false' }}"
                                x-on:change="selectedStock = products.find(p => p.id == $event.target.value)?.current_stock ?? null"
                                class="form-select @error('product_id') form-select--error @enderror">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                        @selected((int) old('product_id', $stockAdjustment->product_id) === $product->id)>
                                    {{ $product->name }} ({{ $product->sku }}) — Stok Saat Ini: {{ number_format($product->current_stock) }}
                                </option>
                            @endforeach
                        </select>
                        <p id="product_id-hint" class="form-hint">Pilih produk yang disesuaikan.</p>
                        <x-form-error field="product_id" />
                    </div>

                    {{-- Info Penyesuaian Sebelumnya --}}
                    <div class="form-group" style="padding:0.875rem 1rem; background-color:var(--color-bg-subtle, #f8fafc); border-radius:0.375rem; border:1px solid var(--color-border);">
                        <p style="font-size:0.875rem; color:var(--color-text-muted); margin:0 0 0.25rem;">
                            <strong>Data Penyesuaian Awal:</strong>
                        </p>
                        <div style="font-size:0.875rem; color:var(--color-text-muted); display:flex; flex-wrap:wrap; gap:1.5rem;">
                            <span>Stok sebelum: <strong style="color:var(--color-text);">{{ number_format($stockAdjustment->stock_before) }} unit</strong></span>
                            <span>Stok sesudah sebelumnya: <strong style="color:var(--color-text);">{{ number_format($stockAdjustment->stock_after) }} unit</strong></span>
                            <span>Selisih awal: <strong style="color:var(--color-text);">{{ $stockAdjustment->difference > 0 ? '+' : '' }}{{ number_format($stockAdjustment->difference) }} unit</strong></span>
                        </div>
                    </div>

                    {{-- Stok Saat Ini (informatif) --}}
                    <div class="form-group" x-show="selectedStock !== null">
                        <p style="font-size:0.875rem; color:var(--color-text-muted);">
                            Stok sistem produk saat ini:
                            <span style="font-weight:600; color:var(--color-text);" x-text="selectedStock !== null ? new Intl.NumberFormat('id-ID').format(selectedStock) + ' unit' : ''"></span>
                        </p>
                    </div>

                    {{-- Jumlah Stok Aktual Baru --}}
                    <div class="form-group">
                        <label for="stock_after" class="form-label">
                            Jumlah Stok Aktual Baru (Hasil Hitung Fisik) <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="stock_after" type="number" name="stock_after"
                               value="{{ old('stock_after', $stockAdjustment->stock_after) }}"
                               required min="0" max="999999"
                               aria-describedby="{{ $errors->has('stock_after') ? 'stock_after-error' : 'stock_after-hint' }}"
                               aria-invalid="{{ $errors->has('stock_after') ? 'true' : 'false' }}"
                               class="form-input @error('stock_after') form-input--error @enderror">
                        <p id="stock_after-hint" class="form-hint">Masukkan koreksi jumlah stok fisik aktual (0 hingga 999.999 unit).</p>
                        <x-form-error field="stock_after" />
                    </div>

                    {{-- Alasan Penyesuaian --}}
                    <div class="form-group">
                        <label for="reason" class="form-label">
                            Alasan Penyesuaian <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <textarea id="reason" name="reason" rows="3" required maxlength="255"
                                  aria-describedby="{{ $errors->has('reason') ? 'reason-error' : 'reason-hint' }}"
                                  aria-invalid="{{ $errors->has('reason') ? 'true' : 'false' }}"
                                  class="form-textarea @error('reason') form-textarea--error @enderror">{{ old('reason', $stockAdjustment->reason) }}</textarea>
                        <p id="reason-hint" class="form-hint">Maksimal 255 karakter. Contoh: Koreksi hasil hitung fisik stok opname, dll.</p>
                        <x-form-error field="reason" />
                    </div>

                    {{-- Tombol Aksi --}}
                    <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem;">
                        <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn--secondary">Batal</a>
                        <button type="submit"
                                :disabled="loading"
                                class="btn btn--primary">
                            <span x-show="!loading">Perbarui Penyesuaian</span>
                            <span x-show="loading" style="display:flex; align-items:center; gap:0.5rem;" aria-live="polite">
                                <svg style="width:1rem;height:1rem;animation:spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
