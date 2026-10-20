<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Log Aktivitas Pengguna', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Log Aktivitas Pengguna</h2>
    </x-slot>

    <div class="page-body">

        {{-- Search Form --}}
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="search-bar">
            <label for="search" class="sr-only">Cari pengguna</label>
            <input id="search"
                   type="text"
                   name="search"
                   value="{{ $search }}"
                   placeholder="Cari nama atau email pengguna..."
                   class="form-input">
            <button type="submit" class="btn btn--secondary">Cari</button>
            @if ($search)
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn--secondary">Reset</a>
            @endif
        </form>

        {{-- Activity Log Table --}}
        <div class="table-wrapper">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Pengguna</th>
                            <th>Email</th>
                            <th>Aksi</th>
                            <th>IP Address</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="font-semibold">
                                    {{ $log->user?->name ?? '(pengguna dihapus)' }}
                                </td>
                                <td>{{ $log->user?->email ?? '-' }}</td>
                                <td>
                                    @if ($log->action === 'login')
                                        <span class="badge badge--green">Login</span>
                                    @elseif ($log->action === 'logout')
                                        <span class="badge badge--gray">Logout</span>
                                    @else
                                        <span class="badge badge--blue">{{ $log->action }}</span>
                                    @endif
                                </td>
                                <td class="font-mono">{{ $log->ip_address }}</td>
                                <td>{{ $log->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB</td>
                            </tr>
                        @empty
                            <tr class="table__empty">
                                <td colspan="5">Tidak ada log aktivitas ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="card__footer">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
