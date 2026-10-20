<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Daftar Produk', 'url' => route('gudang.products.index')],
            ['label' => 'Edit Produk', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Edit Produk</h2>
    </x-slot>

    <div class="page-body">
        <div class="card">
            <div class="card__header">Edit Produk: {{ $product->name }}</div>
            <div class="card__body">
                <form method="POST"
                      action="{{ route('gudang.products.update', $product) }}"
                      novalidate
                      x-data="{ loading: false }"
                      @submit="loading = true">
                    @csrf
                    @method('PUT')

                    {{-- SKU (readonly — bersifat permanen) --}}
                    <div class="form-group">
                        <label for="sku" class="form-label">
                            SKU
                            <span style="color:var(--color-text-muted); font-weight:400; font-size:0.75rem;">(tidak dapat diubah)</span>
                        </label>
                        <input id="sku"
                               type="text"
                               value="{{ $product->sku }}"
                               readonly
                               disabled
                               aria-readonly="true"
                               class="form-input">
                        <p class="form-hint">SKU bersifat permanen dan tidak dapat diubah.</p>
                    </div>

                    {{-- Nama Produk --}}
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Nama Produk <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="name"
                               type="text"
                               name="name"
                               value="{{ old('name', $product->name) }}"
                               required
                               maxlength="255"
                               aria-describedby="name-error"
                               aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                               class="form-input @error('name') form-input--error @enderror">
                        <x-form-error field="name" />
                    </div>

                    {{-- Kategori --}}
                    <div class="form-group">
                        <label for="category_id" class="form-label">
                            Kategori <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <select id="category_id"
                                name="category_id"
                                required
                                aria-describedby="category_id-error"
                                aria-invalid="{{ $errors->has('category_id') ? 'true' : 'false' }}"
                                class="form-select @error('category_id') form-select--error @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-form-error field="category_id" />
                    </div>

                    {{-- Satuan --}}
                    <div class="form-group">
                        <label for="unit" class="form-label">
                            Satuan <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="unit"
                               type="text"
                               name="unit"
                               value="{{ old('unit', $product->unit) }}"
                               required
                               maxlength="50"
                               aria-describedby="unit-error"
                               aria-invalid="{{ $errors->has('unit') ? 'true' : 'false' }}"
                               class="form-input @error('unit') form-input--error @enderror">
                        <x-form-error field="unit" />
                    </div>

                    {{-- Harga Beli --}}
                    <div class="form-group">
                        <label for="purchase_price" class="form-label">
                            Harga Beli (Rp) <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="purchase_price"
                               type="number"
                               name="purchase_price"
                               value="{{ old('purchase_price', $product->purchase_price) }}"
                               required
                               min="0.01"
                               step="0.01"
                               aria-describedby="purchase_price-error"
                               aria-invalid="{{ $errors->has('purchase_price') ? 'true' : 'false' }}"
                               class="form-input @error('purchase_price') form-input--error @enderror">
                        <x-form-error field="purchase_price" />
                    </div>

                    {{-- Harga Jual --}}
                    <div class="form-group">
                        <label for="selling_price" class="form-label">
                            Harga Jual (Rp) <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="selling_price"
                               type="number"
                               name="selling_price"
                               value="{{ old('selling_price', $product->selling_price) }}"
                               required
                               min="0.01"
                               step="0.01"
                               aria-describedby="selling_price-error"
                               aria-invalid="{{ $errors->has('selling_price') ? 'true' : 'false' }}"
                               class="form-input @error('selling_price') form-input--error @enderror">
                        <x-form-error field="selling_price" />
                    </div>

                    {{-- Stok Minimum --}}
                    <div class="form-group">
                        <label for="minimum_stock" class="form-label">
                            Stok Minimum <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="minimum_stock"
                               type="number"
                               name="minimum_stock"
                               value="{{ old('minimum_stock', $product->minimum_stock) }}"
                               required
                               min="1"
                               step="1"
                               aria-describedby="minimum_stock-error"
                               aria-invalid="{{ $errors->has('minimum_stock') ? 'true' : 'false' }}"
                               class="form-input @error('minimum_stock') form-input--error @enderror">
                        <x-form-error field="minimum_stock" />
                    </div>

                    {{-- Stok Saat Ini (info only) --}}
                    <div class="form-group">
                        <p class="form-label">Stok Saat Ini</p>
                        <p style="font-size:var(--text-sm); font-weight:600; color:var(--color-text); margin-top:0.25rem;">
                            {{ number_format($product->current_stock) }} {{ $product->unit }}
                        </p>
                        <p class="form-hint">Stok dikelola melalui modul Stok Masuk dan Penyesuaian Stok.</p>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem;">
                        <a href="{{ route('gudang.products.index') }}" class="btn btn--secondary">Batal</a>
                        <button type="submit"
                                :disabled="loading"
                                class="btn btn--primary">
                            <span x-show="!loading">Perbarui Produk</span>
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
