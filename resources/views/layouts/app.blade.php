<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'Buana Store') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @vite(['resources/js/app.js'])
    @stack('styles')
</head>
<body>
<a href="#main-content" class="skip-link">Lewati ke konten utama</a>

<div x-data="{ sidebarOpen: false }" class="app-shell">

    {{-- Sidebar Overlay (Mobile) --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="sidebar-overlay"
        :class="sidebarOpen ? 'is-visible' : ''"
        aria-hidden="true"
    ></div>

    {{-- SIDEBAR --}}
    <aside
        id="sidebar"
        class="sidebar"
        :class="sidebarOpen ? 'sidebar--open' : ''"
        :style="sidebarOpen ? 'transform:translateX(0)' : ''"
        aria-label="Navigasi utama"
    >
        {{-- Branding --}}
        <div class="sidebar__brand">
            <a href="{{ route('dashboard') }}" class="sidebar__brand-link" aria-label="Buana Store - Beranda">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                </svg>
                <span>Buana Store</span>
            </a>
            <button @click="sidebarOpen = false" class="sidebar__close-btn" aria-label="Tutup sidebar">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar__nav" aria-label="Menu navigasi">
            @php $role = auth()->user()?->role; @endphp

            {{-- ADMIN --}}
            @if ($role === 'admin')
                <span class="sidebar__section-label">Utama</span>
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.dashboard') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dasbor
                </a>

                <span class="sidebar__section-label">Master Data</span>
                <a href="{{ route('admin.categories.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.categories.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.categories.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    Kategori
                </a>
                <a href="{{ route('admin.products.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.products.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.products.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                    Produk
                </a>
                <a href="{{ route('admin.suppliers.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.suppliers.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.suppliers.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    Pemasok
                </a>

                <span class="sidebar__section-label">Transaksi</span>
                <a href="{{ route('admin.stock-ins.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.stock-ins.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.stock-ins.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Stok Masuk
                </a>
                <a href="{{ route('admin.sales.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.sales.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.sales.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Penjualan
                </a>
                <a href="{{ route('admin.stock-adjustments.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.stock-adjustments.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.stock-adjustments.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Penyesuaian Stok
                </a>

                <span class="sidebar__section-label">Laporan &amp; Log</span>
                <a href="{{ route('admin.reports.sales') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.reports.sales') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.reports.sales') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Laporan Penjualan
                </a>
                <a href="{{ route('admin.reports.inventory') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.reports.inventory') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.reports.inventory') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                    Nilai Inventaris
                </a>
                <a href="{{ route('admin.reports.stock-mutation') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.reports.stock-mutation') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.reports.stock-mutation') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Mutasi Stok
                </a>
                <a href="{{ route('admin.notifications.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.notifications.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.notifications.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifikasi
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.users.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.users.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Pengguna
                </a>
                <a href="{{ route('admin.activity-logs.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('admin.activity-logs.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Log Aktivitas
                </a>
            @endif

            {{-- KASIR --}}
            @if ($role === 'kasir')
                <span class="sidebar__section-label">Utama</span>
                <a href="{{ route('kasir.dashboard') }}"
                   class="sidebar__nav-link {{ request()->routeIs('kasir.dashboard') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('kasir.dashboard') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dasbor
                </a>
                <span class="sidebar__section-label">Transaksi</span>
                <a href="{{ route('kasir.sales.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('kasir.sales.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('kasir.sales.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Penjualan
                </a>
            @endif

            {{-- GUDANG --}}
            @if ($role === 'gudang')
                <span class="sidebar__section-label">Utama</span>
                <a href="{{ route('gudang.dashboard') }}"
                   class="sidebar__nav-link {{ request()->routeIs('gudang.dashboard') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('gudang.dashboard') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dasbor
                </a>
                <span class="sidebar__section-label">Stok</span>
                <a href="{{ route('gudang.products.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('gudang.products.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('gudang.products.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                    Produk
                </a>
                <a href="{{ route('gudang.stock-ins.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('gudang.stock-ins.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('gudang.stock-ins.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Stok Masuk
                </a>
                <a href="{{ route('gudang.stock-adjustments.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('gudang.stock-adjustments.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('gudang.stock-adjustments.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    Penyesuaian Stok
                </a>
                <a href="{{ route('gudang.notifications.index') }}"
                   class="sidebar__nav-link {{ request()->routeIs('gudang.notifications.*') ? 'sidebar__nav-link--active' : '' }}"
                   aria-current="{{ request()->routeIs('gudang.notifications.*') ? 'page' : 'false' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notifikasi
                </a>
            @endif
        </nav>

        {{-- User info --}}
        <div class="sidebar__user">
            <div class="sidebar__avatar" aria-hidden="true">{{ mb_substr(auth()->user()?->name ?? '?', 0, 1) }}</div>
            <div class="flex-1 min-w-0">
                <p class="sidebar__user-name">{{ auth()->user()?->name }}</p>
                <p class="sidebar__user-role">{{ auth()->user()?->role }}</p>
            </div>
        </div>
    </aside>

    {{-- MAIN WRAPPER --}}
    <div class="main-wrapper">

        {{-- TOP NAVBAR --}}
        <header class="navbar">
            <div class="navbar__left">
                <button
                    @click="sidebarOpen = true"
                    class="navbar__hamburger"
                    aria-label="Buka sidebar"
                    :aria-expanded="sidebarOpen.toString()"
                    aria-controls="sidebar"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="min-w-0">
                    @if (isset($header)){{ $header }}@endif
                </div>
            </div>

            <div class="navbar__right">
                @php $role = auth()->user()?->role; @endphp
                @if (in_array($role, ['admin', 'gudang']))
                    <x-notification-badge :initialCount="$notificationCount ?? 0" :role="$role" />
                @endif

                {{-- User dropdown --}}
                <div x-data="{ open: false }" class="user-dropdown">
                    <button
                        @click="open = !open"
                        @keydown.escape.window="open = false"
                        class="user-dropdown__trigger"
                        :aria-expanded="open.toString()"
                        aria-haspopup="true"
                        id="user-menu-button"
                    >
                        <span class="truncate" style="max-width:128px">{{ auth()->user()?->name }}</span>
                        <svg style="width:16px;height:16px;color:var(--gray-500)" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click.outside="open = false"
                        @keydown.escape="open = false"
                        class="user-dropdown__menu"
                        :class="open ? 'is-open' : ''"
                        role="menu"
                        aria-orientation="vertical"
                        aria-labelledby="user-menu-button"
                        x-cloak
                    >
                        <a href="{{ route('profile.edit') }}" class="user-dropdown__item" role="menuitem">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Profil Saya
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="user-dropdown__item" role="menuitem">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- PAGE CONTENT --}}
        <main class="page-content" id="main-content">
            @if (isset($breadcrumbs) && !$breadcrumbs->isEmpty())
                <div class="breadcrumb-bar">{{ $breadcrumbs }}</div>
            @endif
            <x-flash-message />
            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')

{{-- Session Expiry Guard --}}
<div x-data="sessionExpiryGuard()" x-init="init()">
    <div
        x-show="showModal"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="modal-backdrop"
        :class="showModal ? 'is-open' : ''"
        role="dialog" aria-modal="true"
        aria-labelledby="session-modal-title"
        aria-describedby="session-modal-desc"
        @keydown.escape.window="if (showModal) continueSession()"
        x-cloak
    >
        <div class="modal">
            <div class="modal__icon modal__icon--warning">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 id="session-modal-title" class="modal__title">Sesi Akan Segera Berakhir</h3>
            <p id="session-modal-desc" class="modal__desc">
                Sesi Anda akan berakhir dalam
                <strong x-text="formatCountdown(countdown)"></strong>.
                Anda memiliki data formulir yang belum disimpan. Ingin tetap di halaman ini?
            </p>
            <div class="modal__actions">
                <button type="button" @click="goToLogin()" class="btn btn--secondary">Ke Halaman Login</button>
                <button type="button" @click="continueSession()" class="btn btn--primary">Tetap di Halaman Ini</button>
            </div>
        </div>
    </div>
</div>

<script>
function sessionExpiryGuard() {
    return {
        formDirty: false, showModal: false, countdown: 0,
        _pollInterval: null, _countdownInterval: null,
        warningThreshold: 120,
        sessionLifetime: {{ config('session.lifetime', 60) * 60 }},
        init() { this.setupFormDirtyTracking(); this.startSessionPolling(); },
        setupFormDirtyTracking() {
            const trackForm = (form) => {
                const inputs = form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('change', () => { this.formDirty = true; });
                    input.addEventListener('input',  () => { this.formDirty = true; });
                });
                form.addEventListener('submit', () => { this.formDirty = false; });
            };
            document.querySelectorAll('form').forEach(trackForm);
            const observer = new MutationObserver((mutations) => {
                mutations.forEach(mutation => {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) {
                            if (node.tagName === 'FORM') trackForm(node);
                            node.querySelectorAll && node.querySelectorAll('form').forEach(trackForm);
                        }
                    });
                });
            });
            observer.observe(document.body, { childList: true, subtree: true });
        },
        startSessionPolling() {
            this._pollInterval = setInterval(async () => {
                if (!this.formDirty) return;
                try {
                    const response = await fetch('/api/notifications/count', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin',
                    });
                    if (response.status === 401) { this.triggerSessionExpiry(); return; }
                    const timeLeft = parseInt(response.headers.get('X-Session-Time-Left') || '9999');
                    if (timeLeft <= this.warningThreshold && this.formDirty) {
                        this.countdown = timeLeft; this.showModal = true; this.startCountdown();
                    }
                } catch (e) {}
            }, 60000);
        },
        startCountdown() {
            if (this._countdownInterval) return;
            this._countdownInterval = setInterval(() => {
                this.countdown = Math.max(0, this.countdown - 1);
                if (this.countdown <= 0) { clearInterval(this._countdownInterval); this.goToLogin(); }
            }, 1000);
        },
        triggerSessionExpiry() {
            clearInterval(this._pollInterval);
            if (this.formDirty) { this.countdown = 0; this.showModal = true; } else { this.goToLogin(); }
        },
        continueSession() {
            fetch('/api/notifications/count', { credentials: 'same-origin' }).catch(() => {});
            this.showModal = false; clearInterval(this._countdownInterval); this._countdownInterval = null;
            this.startSessionPolling();
        },
        goToLogin() {
            clearInterval(this._pollInterval); clearInterval(this._countdownInterval);
            window.location.href = '{{ route("login") }}';
        },
        formatCountdown(seconds) {
            if (seconds <= 0) return '0 detik';
            const m = Math.floor(seconds / 60), s = seconds % 60;
            if (m > 0) return `${m} menit ${s} detik`;
            return `${s} detik`;
        },
    };
}
</script>
</body>
</html>
