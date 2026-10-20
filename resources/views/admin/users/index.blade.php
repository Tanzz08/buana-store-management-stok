<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Manajemen Pengguna', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="navbar__page-title">Manajemen Pengguna</h2>
            <a href="{{ route('admin.users.create') }}" class="btn btn--primary btn--sm">
                + Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="page-body">

        {{-- Search Form --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="search-bar">
            <label for="search" class="sr-only">Cari pengguna</label>
            <input id="search"
                   type="text"
                   name="search"
                   value="{{ $search }}"
                   placeholder="Cari nama atau email..."
                   class="form-input">
            <button type="submit" class="btn btn--secondary">Cari</button>
            @if ($search)
                <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">Reset</a>
            @endif
        </form>

        {{-- Users Table --}}
        <div class="table-wrapper">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Peran</th>
                            <th>Status</th>
                            <th>Verifikasi Email</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr style="{{ $user->is_active ? '' : 'opacity: 0.65;' }}">
                                <td class="font-semibold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $roleLabels = ['admin' => 'Admin', 'kasir' => 'Kasir', 'gudang' => 'Gudang'];
                                        $roleBadge  = ['admin' => 'badge--indigo', 'kasir' => 'badge--blue', 'gudang' => 'badge--green'];
                                    @endphp
                                    <span class="badge {{ $roleBadge[$user->role] ?? 'badge--gray' }}">
                                        {{ $roleLabels[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge badge--green">Aktif</span>
                                    @else
                                        <span class="badge badge--red">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->email_verified_at)
                                        <span class="text-green-600 text-sm">Terverifikasi</span>
                                    @else
                                        <span class="text-yellow-600 text-sm">Belum Terverifikasi</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table__actions">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-link">Edit</a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST"
                                                  action="{{ route('admin.users.destroy', $user) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Nonaktifkan akun {{ addslashes($user->name) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-link btn-link--danger">
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr class="table__empty">
                                <td colspan="6">Tidak ada pengguna ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="card__footer">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
