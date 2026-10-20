<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Manajemen Kategori', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            <h2 class="navbar__page-title">Manajemen Kategori</h2>
            <a href="{{ route('admin.categories.create') }}" class="btn btn--primary btn--sm">+ Tambah Kategori</a>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="table-wrapper">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Jumlah Produk</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->description ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge--indigo">{{ $category->products_count }}</span>
                                </td>
                                <td class="text-right">
                                    <div class="table__actions">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn-link">
                                            Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.categories.destroy', $category) }}"
                                              class="inline"
                                              x-data
                                              @submit.prevent="
                                                if (confirm('Hapus kategori &quot;{{ addslashes($category->name) }}&quot;? Tindakan ini tidak dapat dibatalkan.')) {
                                                    $el.submit();
                                                }
                                              ">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-link btn-link--danger">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="table__empty">
                                <td colspan="4">
                                    Belum ada kategori. Klik <a href="{{ route('admin.categories.create') }}" class="btn-link">+ Tambah Kategori</a> untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
