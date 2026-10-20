<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Manajemen Produk', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            <h2 class="navbar__page-title">Manajemen Produk</h2>
            <a href="{{ route('admin.products.create') }}" class="btn btn--primary btn--sm">+ Tambah Produk</a>
        </div>
    </x-slot>

    <div class="page-body">
        {{-- Search Form --}}
        <form method="GET" action="{{ route('admin.products.index') }}" role="search" class="search-bar">
            <label for="q" class="sr-only">Cari produk</label>
            <input id="q"
                   type="search"
                   name="q"
                   value="{{ $keyword }}"
                   placeholder="Cari nama, SKU, atau kategori…"
                   class="form-input">
            <button type="submit" class="btn btn--secondary">Cari</button>
            @if ($keyword)
                <a href="{{ route('admin.products.index') }}" class="btn btn--secondary">Reset</a>
            @endif
        </form>

        {{-- Product Table --}}
        <div class="table-wrapper">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-right">Stok</th>
                            <th class="text-center">Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td class="font-mono">{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td class="text-right">Rp {{ number_format((float) $product->selling_price, 0, ',', '.') }}</td>
                                <td class="text-right">
                                    @if ($product->current_stock <= $product->minimum_stock)
                                        <span style="color:var(--color-error);font-weight:600;">{{ number_format($product->current_stock) }}</span>
                                    @else
                                        {{ number_format($product->current_stock) }}
                                    @endif
                                    <span class="form-hint" style="display:inline;">/ min. {{ $product->minimum_stock }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($product->status === 'active')
                                        <span class="badge badge--green">Aktif</span>
                                    @else
                                        <span class="badge badge--gray">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="table__actions">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn-link">Edit</a>

                                        @if ($product->status === 'active')
                                            <form method="POST"
                                                  action="{{ route('admin.products.deactivate', $product) }}"
                                                  class="inline"
                                                  x-data
                                                  @submit.prevent="
                                                    if (confirm('Nonaktifkan produk &quot;{{ addslashes($product->name) }}&quot;?')) {
                                                        $el.submit();
                                                    }
                                                  ">
                                                @csrf
                                                <button type="submit" class="btn-link btn-link--warning">Nonaktifkan</button>
                                            </form>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('admin.products.activate', $product) }}"
                                                  class="inline">
                                                @csrf
                                                <button type="submit" class="btn-link btn-link--success">Aktifkan</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="table__empty">
                                <td colspan="7">
                                    @if ($keyword)
                                        Tidak ada produk yang cocok dengan "{{ $keyword }}".
                                    @else
                                        Belum ada produk.
                                        <a href="{{ route('admin.products.create') }}" class="btn-link">+ Tambah Produk</a> untuk memulai.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if (! $keyword && $products instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div style="margin-top:1rem;">
                {{ $products->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
