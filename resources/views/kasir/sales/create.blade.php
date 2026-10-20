<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Riwayat Penjualan', 'url' => route('kasir.sales.index')],
            ['label' => 'Transaksi Baru', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Transaksi Baru</h2>
    </x-slot>

    <div class="page-body">

        {{-- Error Flash --}}
        <x-flash-message />

        {{-- Validation errors --}}
        @if ($errors->any())
            <div role="alert" class="alert alert--error" style="margin-bottom:1rem;">
                <p style="font-weight:600; margin-bottom:0.25rem;">Terjadi kesalahan validasi:</p>
                <ul style="list-style:disc; padding-left:1.25rem; font-size:var(--text-sm);">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card"
             x-data="saleForm({{ $products->toJson() }})"
             x-init="init()">

            <div class="card__header">Form Transaksi Penjualan</div>

            <div class="card__body">
                <form method="POST" action="{{ route('kasir.sales.store') }}" novalidate
                      @submit.prevent="submitForm($event)"
                      id="sale-form">
                    @csrf

                    {{-- Pencarian Produk --}}
                    <div class="form-group">
                        <label for="product-search" class="form-label">
                            Cari &amp; Tambah Produk
                        </label>
                        <div style="position:relative;">
                            <input id="product-search"
                                   type="text"
                                   role="combobox"
                                   aria-autocomplete="list"
                                   aria-controls="product-search-listbox"
                                   :aria-expanded="showDropdown && searchResults.length > 0 ? 'true' : 'false'"
                                   aria-haspopup="listbox"
                                   x-model="searchQuery"
                                   @input.debounce.300ms="searchProducts()"
                                   @focus="showDropdown = searchQuery.length > 0 && searchResults.length > 0"
                                   @click.away="showDropdown = false"
                                   placeholder="Ketik nama atau SKU produk..."
                                   autocomplete="off"
                                   class="form-input">
                            {{-- Dropdown hasil pencarian --}}
                            <ul id="product-search-listbox"
                                role="listbox"
                                aria-label="Hasil pencarian produk"
                                x-show="showDropdown && searchResults.length > 0"
                                x-cloak
                                style="position:absolute; z-index:10; margin-top:0.25rem; width:100%; background:white; border:1px solid var(--color-border); border-radius:var(--radius-md); box-shadow:var(--shadow-md); max-height:15rem; overflow-y:auto; list-style:none; padding:0; margin-bottom:0;">
                                <template x-for="product in searchResults" :key="product.id">
                                    <li role="option" :aria-selected="false" style="margin:0;">
                                        <button type="button"
                                                @click="addItem(product); showDropdown = false; searchQuery = ''"
                                                style="width:100%; text-align:left; padding:0.5rem 1rem; background:none; border:none; cursor:pointer; font-size:var(--text-sm);">
                                            <span style="font-weight:500;" x-text="product.name"></span>
                                            <span style="color:var(--color-text-muted); margin-left:0.25rem;" x-text="'(' + product.sku + ')'"></span>
                                            <span style="float:right; color:var(--color-text-muted);">
                                                Stok: <span x-text="product.current_stock"></span>
                                                &nbsp;| Rp <span x-text="formatNumber(product.selling_price)"></span>
                                            </span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                            <div x-show="showDropdown && searchResults.length === 0 && searchQuery.length >= 2"
                                 x-cloak
                                 role="status"
                                 aria-live="polite"
                                 style="position:absolute; z-index:10; margin-top:0.25rem; width:100%; background:white; border:1px solid var(--color-border); border-radius:var(--radius-md); box-shadow:var(--shadow-md); padding:0.75rem 1rem; font-size:var(--text-sm); color:var(--color-text-muted);">
                                Produk tidak ditemukan.
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Item Penjualan --}}
                    <div class="form-group">
                        <div class="table-scroll">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-right">Harga Jual</th>
                                        <th class="text-right" style="width:8rem;">Jumlah</th>
                                        <th class="text-right">Subtotal</th>
                                        <th class="text-right" style="width:4rem;">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="items.length === 0">
                                        <tr class="table__empty">
                                            <td colspan="5">
                                                Belum ada produk ditambahkan. Cari produk di atas.
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="(item, index) in items" :key="item._key">
                                        <tr>
                                            <td>
                                                {{-- Hidden inputs untuk form submission --}}
                                                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                                <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
                                                <div style="font-weight:500; font-size:var(--text-sm);" x-text="item.name"></div>
                                                <div style="font-size:0.75rem; color:var(--color-text-muted);" x-text="item.sku"></div>
                                                <div style="font-size:0.75rem; color:var(--color-text-muted);">
                                                    Stok tersedia: <span x-text="item.available_stock"></span>
                                                </div>
                                            </td>
                                            <td class="text-right" style="font-size:var(--text-sm); white-space:nowrap;">
                                                Rp <span x-text="formatNumber(item.unit_price)"></span>
                                            </td>
                                            <td class="text-right">
                                                <input type="number"
                                                       :id="'item-qty-' + item._key"
                                                       :value="item.quantity"
                                                       @change="updateQuantity(index, $event.target.value)"
                                                       min="1"
                                                       :max="item.available_stock"
                                                       :aria-label="'Jumlah ' + item.name"
                                                       :aria-describedby="item.quantity > item.available_stock ? 'qty-error-' + item._key : null"
                                                       :aria-invalid="item.quantity > item.available_stock ? 'true' : 'false'"
                                                       class="form-input"
                                                       style="width:6rem; text-align:right; margin-left:auto;"
                                                       :class="item.quantity > item.available_stock ? 'form-input--error' : ''">
                                                <p :id="'qty-error-' + item._key"
                                                   x-show="item.quantity > item.available_stock"
                                                   role="alert"
                                                   class="form-error"
                                                   style="text-align:right; margin-top:0.25rem;">
                                                    Melebihi stok!
                                                </p>
                                            </td>
                                            <td class="text-right" style="font-weight:600; white-space:nowrap;">
                                                Rp <span x-text="formatNumber(item.subtotal)"></span>
                                            </td>
                                            <td class="text-right">
                                                <button type="button"
                                                        @click="removeItem(index)"
                                                        :aria-label="'Hapus ' + item.name"
                                                        class="btn-link btn-link--danger">
                                                    Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot x-show="items.length > 0">
                                    <tr>
                                        <td colspan="3" class="text-right" style="font-weight:600;">
                                            Total Transaksi:
                                        </td>
                                        <td class="text-right" style="font-weight:700; font-size:1rem; white-space:nowrap;">
                                            Rp <span x-text="formatNumber(grandTotal)"></span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- Tombol Aksi --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; padding-top:1rem; border-top:1px solid var(--color-border);">
                        <button type="button"
                                @click="confirmCancel()"
                                class="btn btn--secondary">
                            Batalkan Transaksi
                        </button>
                        <button type="submit"
                                :disabled="loading || items.length === 0 || hasInvalidQuantity"
                                class="btn btn--primary">
                            <span x-show="!loading">Simpan Transaksi</span>
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

    @push('scripts')
    <script>
    function saleForm(allProducts) {
        return {
            items: [],
            searchQuery: '',
            searchResults: [],
            showDropdown: false,
            loading: false,
            _counter: 0,
            allProducts: allProducts,

            init() {
                @if (old('items'))
                const oldItems = @json(old('items'));
                if (oldItems && Array.isArray(oldItems)) {
                    oldItems.forEach((item, i) => {
                        const product = this.allProducts.find(p => p.id == item.product_id);
                        if (product) {
                            this.addItem(product, parseInt(item.quantity) || 1);
                        }
                    });
                }
                @endif
            },

            addItem(product, qty = 1) {
                const existing = this.items.find(i => i.product_id === product.id);
                if (existing) {
                    const newQty = existing.quantity + qty;
                    existing.quantity = Math.min(newQty, existing.available_stock);
                    existing.subtotal = Math.round(existing.quantity * existing.unit_price * 100) / 100;
                    return;
                }
                this._counter++;
                this.items.push({
                    _key: this._counter,
                    product_id: product.id,
                    name: product.name,
                    sku: product.sku,
                    unit_price: parseFloat(product.selling_price),
                    quantity: Math.min(qty, product.current_stock > 0 ? product.current_stock : qty),
                    available_stock: product.current_stock,
                    subtotal: Math.round(parseFloat(product.selling_price) * Math.min(qty, product.current_stock > 0 ? product.current_stock : qty) * 100) / 100,
                });
            },

            removeItem(index) {
                this.items.splice(index, 1);
            },

            updateQuantity(index, value) {
                const qty = parseInt(value) || 1;
                this.items[index].quantity = qty;
                this.items[index].subtotal = Math.round(qty * this.items[index].unit_price * 100) / 100;
            },

            searchProducts() {
                const q = this.searchQuery.trim().toLowerCase();
                if (q.length < 1) {
                    this.searchResults = [];
                    this.showDropdown = false;
                    return;
                }
                this.searchResults = this.allProducts.filter(p =>
                    p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q)
                ).slice(0, 15);
                this.showDropdown = this.searchResults.length > 0;
            },

            get grandTotal() {
                return Math.round(this.items.reduce((sum, i) => sum + i.subtotal, 0) * 100) / 100;
            },

            get hasInvalidQuantity() {
                return this.items.some(i => i.quantity > i.available_stock || i.quantity < 1);
            },

            formatNumber(value) {
                return Number(value).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            },

            confirmCancel() {
                if (this.items.length > 0) {
                    if (confirm('Apakah Anda yakin ingin membatalkan transaksi? Semua item yang sudah ditambahkan akan dihapus.')) {
                        this.items = [];
                    }
                } else {
                    window.location.href = '{{ route('kasir.sales.index') }}';
                }
            },

            submitForm(event) {
                if (this.items.length === 0) return;
                if (this.hasInvalidQuantity) return;
                this.loading = true;
                setTimeout(() => {
                    event.target.submit();
                }, 50);
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
