<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Dasbor', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Dasbor Admin</h2>
    </x-slot>

    <div class="page-body">
        <div class="space-y-6">

            {{-- Summary Cards --}}
            <div class="grid grid--4">

                {{-- Total Produk Aktif --}}
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--indigo" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Produk Aktif</p>
                        <p class="stat-card__value">
                            {{ number_format($summary['total_active_products']) }}
                        </p>
                    </div>
                </div>

                {{-- Total Nilai Inventaris --}}
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--green" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Nilai Inventaris</p>
                        <p class="stat-card__value" style="font-size: var(--text-xl)">
                            Rp {{ number_format($summary['total_inventory_value'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                {{-- Transaksi Hari Ini --}}
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--blue" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Transaksi Hari Ini</p>
                        <p class="stat-card__value">
                            {{ number_format($summary['today_transactions']) }}
                        </p>
                    </div>
                </div>

                {{-- Stok Menipis --}}
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--red" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Stok Menipis</p>
                        <p class="stat-card__value">
                            {{ number_format($summary['low_stock_count']) }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- Tren Penjualan 30 Hari --}}
            <div class="card">
                <div class="card__header">Tren Penjualan 30 Hari Terakhir</div>
                <div class="card__body">
                    <div class="chart-container">
                        <canvas id="salesChart" aria-label="Grafik tren penjualan 30 hari terakhir" role="img"></canvas>
                    </div>
                </div>
            </div>

            {{-- Shortcut Laporan --}}
            <div class="card">
                <div class="card__header">Laporan Cepat</div>
                <div class="card__body">
                    <div class="shortcut-grid">
                        <a href="{{ route('admin.reports.sales') }}" class="btn btn--primary">
                            Laporan Penjualan &amp; Keuntungan
                        </a>
                        <a href="{{ route('admin.reports.inventory') }}" class="btn btn--secondary">
                            Nilai Inventaris
                        </a>
                        <a href="{{ route('admin.reports.stock-mutation') }}" class="btn btn--warning">
                            Mutasi Stok
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const raw = @json(json_decode($chartData));
            const ctx = document.getElementById('salesChart').getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: raw.labels,
                    datasets: [{
                        label: 'Penjualan (Rp)',
                        data: raw.values,
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: val => 'Rp ' + val.toLocaleString('id-ID'),
                            },
                        },
                    },
                },
            });
        })();
    </script>
    @endpush
</x-app-layout>
