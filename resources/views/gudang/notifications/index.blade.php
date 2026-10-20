<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Notifikasi Stok Rendah', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
            <h2 class="navbar__page-title">
                Notifikasi Stok Rendah
                @if ($unreadCount > 0)
                    <span class="notif-badge" style="position:relative;top:auto;right:auto;display:inline-flex;margin-left:var(--space-2)">
                        {{ $unreadCount }}
                    </span>
                @endif
            </h2>
            @if ($notifications->whereIn('status', ['unread', 'read'])->isNotEmpty())
                <form
                    method="POST"
                    action="{{ route('gudang.notifications.read-all') }}"
                    onsubmit="return confirm('Tandai semua notifikasi sebagai sudah dibaca?')"
                >
                    @csrf
                    <button type="submit" class="btn btn--primary btn--sm">
                        Tandai Semua Sudah Dibaca
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="page-body">

        {{-- Daftar Notifikasi --}}
        <div class="table-wrapper">
            @if ($notifications->isEmpty())
                <div class="table__empty">
                    <p>Tidak ada notifikasi stok rendah saat ini.</p>
                </div>
            @else
                <ul role="list" style="list-style:none;margin:0;padding:0">
                    @foreach ($notifications as $notification)
                        @php
                            $isReadByUser = $notification->reads->isNotEmpty();
                            $isResolved   = $notification->status === 'resolved';
                            $isUnread     = $notification->status === 'unread' && ! $isReadByUser;
                        @endphp
                        <li style="display:flex; align-items:flex-start; gap:1rem; padding:1.5rem; border-bottom:1px solid var(--color-border); {{ $isUnread ? 'background-color:#fffbeb;' : '' }}">

                            {{-- Status indicator dot --}}
                            <div style="flex-shrink:0; margin-top:2px;" aria-hidden="true">
                                @if ($isResolved)
                                    <span style="display:inline-block;width:10px;height:10px;border-radius:9999px;background-color:var(--color-success)" title="Stok sudah pulih"></span>
                                @elseif ($isUnread)
                                    <span style="display:inline-block;width:10px;height:10px;border-radius:9999px;background-color:var(--color-error)" title="Belum dibaca"></span>
                                @else
                                    <span style="display:inline-block;width:10px;height:10px;border-radius:9999px;background-color:var(--gray-300)" title="Sudah dibaca"></span>
                                @endif
                            </div>

                            {{-- Konten notifikasi --}}
                            <div style="flex:1; min-width:0;">
                                <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                                    <span style="font-weight:600; font-size:var(--text-sm);">
                                        {{ $notification->product->name ?? '(Produk dihapus)' }}
                                    </span>
                                    <span style="font-size:0.75rem; color:var(--color-text-muted);">
                                        SKU: {{ $notification->product->sku ?? '-' }}
                                    </span>

                                    @if ($isResolved)
                                        <span class="badge badge--green">Terselesaikan</span>
                                    @elseif ($isUnread)
                                        <span class="badge badge--red">Belum Dibaca</span>
                                    @else
                                        <span class="badge badge--gray">Sudah Dibaca</span>
                                    @endif
                                </div>

                                <p style="font-size:var(--text-sm); color:var(--color-text-muted); margin-top:0.25rem;">
                                    Stok saat notifikasi dibuat:
                                    <strong style="color:var(--color-error);">{{ number_format($notification->stock_at_creation) }}</strong>
                                    unit &bull;
                                    Stok minimum:
                                    <strong>{{ number_format($notification->product->minimum_stock ?? 0) }}</strong>
                                    unit
                                </p>

                                @if ($notification->product)
                                    <p style="font-size:var(--text-sm); color:var(--color-text-muted); margin-top:2px;">
                                        Stok saat ini:
                                        <strong style="{{ $notification->product->current_stock <= $notification->product->minimum_stock ? 'color:var(--color-error)' : 'color:var(--color-success)' }}">
                                            {{ number_format($notification->product->current_stock) }}
                                        </strong>
                                        unit
                                    </p>
                                @endif

                                <p style="font-size:0.75rem; color:var(--color-text-muted); margin-top:0.25rem;">
                                    {{ $notification->created_at->translatedFormat('d M Y, H:i') }}
                                    @if ($notification->resolved_at)
                                        &bull; Diselesaikan: {{ $notification->resolved_at->translatedFormat('d M Y, H:i') }}
                                    @endif
                                </p>
                            </div>

                            {{-- Aksi --}}
                            @if (! $isReadByUser && ! $isResolved)
                                <div style="flex-shrink:0;">
                                    <form
                                        method="POST"
                                        action="{{ route('gudang.notifications.read', $notification) }}"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            class="btn btn--secondary btn--sm"
                                            aria-label="Tandai notifikasi untuk {{ $notification->product->name ?? 'produk ini' }} sebagai sudah dibaca"
                                        >
                                            Tandai Dibaca
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</x-app-layout>
