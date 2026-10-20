<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Riwayat Stok Masuk', 'url' => route('gudang.stock-ins.index')],
            ['label' => 'Catat Stok Masuk', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Catat Stok Masuk</h2>
    </x-slot>

    <div class="page-body">
        <div class="card">
            <div class="card__header">Form Pencatatan Stok Masuk</div>
            <div class="card__body">
                <form method="POST" action="{{ route('gudang.stock-ins.store') }}" novalidate
                      x-data="{ loading: false }"
                      @submit="loading = true">
                    @csrf

                    {{-- Produk --}}
                    <div class="form-group">
                        <label for="product_id" class="form-label">
                            Produk <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <select id="product_id" name="product_id" required
                                aria-describedby="{{ $errors->has('product_id') ? 'product_id-error' : '' }}"
                                aria-invalid="{{ $errors->has('product_id') ? 'true' : 'false' }}"
                                class="form-select @error('product_id') form-select--error @enderror">
                            <option value="">-- Pilih Produk --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}"
                                        @selected(old('product_id') == $product->id)>
                                    {{ $product->name }} ({{ $product->sku }})
                                </option>
                            @endforeach
                        </select>
                        <x-form-error field="product_id" />
                    </div>

                    {{-- Pemasok --}}
                    <div class="form-group">
                        <label for="supplier_id" class="form-label">
                            Pemasok <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <select id="supplier_id" name="supplier_id" required
                                aria-describedby="{{ $errors->has('supplier_id') ? 'supplier_id-error' : '' }}"
                                aria-invalid="{{ $errors->has('supplier_id') ? 'true' : 'false' }}"
                                class="form-select @error('supplier_id') form-select--error @enderror">
                            <option value="">-- Pilih Pemasok --</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"
                                        @selected(old('supplier_id') == $supplier->id)>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-form-error field="supplier_id" />
                    </div>

                    {{-- Jumlah Unit --}}
                    <div class="form-group">
                        <label for="quantity" class="form-label">
                            Jumlah Unit <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="quantity" type="number" name="quantity"
                               value="{{ old('quantity') }}"
                               required min="1" max="999999"
                               aria-describedby="{{ $errors->has('quantity') ? 'quantity-error' : 'quantity-hint' }}"
                               aria-invalid="{{ $errors->has('quantity') ? 'true' : 'false' }}"
                               class="form-input @error('quantity') form-input--error @enderror">
                        <p id="quantity-hint" class="form-hint">Antara 1 hingga 999.999 unit.</p>
                        <x-form-error field="quantity" />
                    </div>

                    {{-- Harga Beli Per Unit --}}
                    <div class="form-group">
                        <label for="unit_price" class="form-label">
                            Harga Beli Per Unit (Rp) <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="unit_price" type="number" name="unit_price"
                               value="{{ old('unit_price') }}"
                               required min="0.01" max="999999999.99" step="0.01"
                               aria-describedby="{{ $errors->has('unit_price') ? 'unit_price-error' : 'unit_price-hint' }}"
                               aria-invalid="{{ $errors->has('unit_price') ? 'true' : 'false' }}"
                               class="form-input @error('unit_price') form-input--error @enderror">
                        <p id="unit_price-hint" class="form-hint">Minimal Rp 0,01 per unit.</p>
                        <x-form-error field="unit_price" />
                    </div>

                    {{-- Tanggal Penerimaan --}}
                    <div class="form-group">
                        <label for="received_at" class="form-label">
                            Tanggal Penerimaan <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="received_at" type="date" name="received_at"
                               value="{{ old('received_at', now()->toDateString()) }}"
                               required max="{{ now()->toDateString() }}"
                               aria-describedby="{{ $errors->has('received_at') ? 'received_at-error' : '' }}"
                               aria-invalid="{{ $errors->has('received_at') ? 'true' : 'false' }}"
                               class="form-input @error('received_at') form-input--error @enderror">
                        <x-form-error field="received_at" />
                    </div>

                    {{-- Tombol Aksi --}}
                    <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem;">
                        <a href="{{ route('gudang.stock-ins.index') }}" class="btn btn--secondary">Batal</a>
                        <button type="submit"
                                :disabled="loading"
                                class="btn btn--primary">
                            <span x-show="!loading">Simpan Stok Masuk</span>
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
