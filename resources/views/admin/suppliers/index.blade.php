<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Manajemen Pemasok', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            <h2 class="navbar__page-title">Manajemen Pemasok</h2>
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn--primary btn--sm">+ Tambah Pemasok</a>
        </div>
    </x-slot>

    <div class="page-body">
        <div class="table-wrapper">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Pemasok</th>
                            <th>Nomor Telepon</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($suppliers as $supplier)
                            <tr>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->phone ?? '-' }}</td>
                                <td>{{ $supplier->email ?? '-' }}</td>
                                <td>{{ $supplier->address ?? '-' }}</td>
                                <td class="text-right">
                                    <div class="table__actions">
                                        <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn-link">
                                            Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.suppliers.destroy', $supplier) }}"
                                              class="inline"
                                              x-data
                                              @submit.prevent="
                                                if (confirm('Hapus pemasok &quot;{{ addslashes($supplier->name) }}&quot;? Tindakan ini tidak dapat dibatalkan.')) {
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
                                <td colspan="5">
                                    Belum ada pemasok. Klik <a href="{{ route('admin.suppliers.create') }}" class="btn-link">+ Tambah Pemasok</a> untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
