{{--
    Komponen badge notifikasi stok rendah untuk navbar.

    Menampilkan jumlah notifikasi yang belum dibaca.
    Badge disembunyikan secara otomatis ketika count = 0.
    Polling ke /api/notifications/count setiap 30 detik via Alpine.js.

    Props:
        $initialCount  — jumlah notifikasi unread dari server saat halaman dimuat
        $role          — 'admin' atau 'gudang', menentukan URL halaman notifikasi
--}}
@props(['initialCount' => 0, 'role' => 'admin'])

@php
    $notificationsRoute = $role === 'gudang'
        ? route('gudang.notifications.index')
        : route('admin.notifications.index');
@endphp

<div
    x-data="{
        count: {{ (int) $initialCount }},
        init() {
            this.poll();
        },
        poll() {
            setInterval(async () => {
                try {
                    const res  = await fetch('/api/notifications/count', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    const data = await res.json();
                    this.count = data.count ?? 0;
                } catch (e) {
                    // Abaikan kesalahan jaringan sementara
                }
            }, 30000);
        }
    }"
    x-init="init()"
    role="status"
    aria-live="polite"
    aria-atomic="true"
>
    <a
        href="{{ $notificationsRoute }}"
        class="notif-btn"
        aria-label="Notifikasi stok rendah"
        :aria-label="count > 0 ? `${count} notifikasi stok rendah belum dibaca` : 'Notifikasi stok rendah'"
    >
        {{-- Ikon lonceng --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        {{-- Badge count — disembunyikan jika count = 0 --}}
        <span
            x-show="count > 0"
            x-text="count"
            x-transition
            class="notif-badge"
            aria-hidden="true"
        ></span>
    </a>
</div>
