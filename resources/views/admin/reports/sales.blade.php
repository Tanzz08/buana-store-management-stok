<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Laporan', 'url' => route('admin.reports.sales')],
            ['label' => 'Hasil Penjualan & Keuntungan', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="navbar__page-title">Laporan Hasil Penjualan &amp; Keuntungan</h2>
            <div class="flex items-center gap-2 no-print">
                <button type="button" onclick="window.print()" class="btn btn--secondary btn--sm">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Cetak / PDF
                </button>
                <a href="{{ route('admin.reports.export', ['type' => 'sales', 'start_date' => $startDate, 'end_date' => $endDate, 'cashier_id' => $cashierId]) }}"
                   class="btn btn--success btn--sm">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Ekspor Excel
                </a>
            </div>
        </div>
    </x-slot>

    @push('styles')
    <style>
        .print-only { display: none; }
        @media print {
            .no-print, .sidebar, .sidebar-overlay, .navbar, .breadcrumb-bar, .user-dropdown { display: none !important; }
            .app-shell { display: block !important; }
            .main-wrapper { margin: 0 !important; width: 100% !important; max-width: 100% !important; }
            .page-body { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .print-only { display: block !important; margin-bottom: 1.5rem; border-bottom: 2px solid #333; padding-bottom: 0.75rem; }
            .card, .table-wrapper { box-shadow: none !important; border: 1px solid #ccc !important; }
            .chart-wrapper { page-break-after: avoid; }
            body { font-size: 12px; background: #fff !important; color: #000 !important; }
            .stat-card { border: 1px solid #ccc !important; }
        }
        .preset-btn {
            background-color: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
            padding: 0.25rem 0.625rem;
            border-radius: var(--radius-sm);
            font-size: var(--text-xs);
            font-weight: 500;
            cursor: pointer;
            transition: all var(--transition-fast);
        }
        .preset-btn:hover {
            background-color: var(--color-primary-bg);
            color: var(--color-primary);
            border-color: var(--color-primary);
        }
    </style>
    @endpush

    <div class="page-body">
        <div class="space-y-6">

            {{-- Kop Cetak Khusus Print Mode --}}
            <div class="print-only">
                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                    <div>
                        <h1 style="font-size:20px; font-weight:800; margin:0;">BUANA STORE</h1>
                        <p style="font-size:12px; color:#555; margin:2px 0 0 0;">Laporan Monitoring Hasil Penjualan &amp; Keuntungan</p>
                    </div>
                    <div style="text-align:right; font-size:11px; color:#444;">
                        <p style="margin:0;"><strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</p>
                        <p style="margin:2px 0 0 0;"><strong>Kasir:</strong> {{ $cashierId ? ($cashiers->firstWhere('id', $cashierId)?->name ?? 'Kasir Tertentu') : 'Semua Kasir' }}</p>
                        <p style="margin:2px 0 0 0;"><strong>Dicetak:</strong> {{ now()->translatedFormat('d M Y, H:i') }} oleh {{ auth()->user()?->name }}</p>
                    </div>
                </div>
            </div>

            {{-- Navigasi Tab Laporan --}}
            <x-report-nav-tabs />

            {{-- Filter Panel --}}
            <div class="card no-print">
                <div class="card__body">
                    <form id="filterForm" method="GET" action="{{ route('admin.reports.sales') }}">
                        <div style="display:flex; flex-direction:column; gap:1rem;">
                            {{-- Filter Inputs --}}
                            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                                <div class="form-group" style="margin-bottom:0">
                                    <label for="start_date" class="form-label font-semibold">Tanggal Mulai</label>
                                    <input type="date"
                                           id="start_date"
                                           name="start_date"
                                           value="{{ $startDate }}"
                                           class="form-input">
                                </div>
                                <div class="form-group" style="margin-bottom:0">
                                    <label for="end_date" class="form-label font-semibold">Tanggal Akhir</label>
                                    <input type="date"
                                           id="end_date"
                                           name="end_date"
                                           value="{{ $endDate }}"
                                           class="form-input">
                                </div>
                                <div class="form-group" style="margin-bottom:0">
                                    <label for="cashier_id" class="form-label font-semibold">Pilih Kasir</label>
                                    <select id="cashier_id" name="cashier_id" class="form-select">
                                        <option value="">Semua Kasir</option>
                                        @foreach ($cashiers as $c)
                                            <option value="{{ $c->id }}" @selected((int) $cashierId === $c->id)>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Shortcut Presets & Buttons --}}
                            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:0.75rem; border-top:1px solid var(--gray-100); padding-top:0.75rem;">
                                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:0.375rem;">
                                    <span style="font-size:var(--text-xs); color:var(--gray-500); font-weight:600; margin-right:0.25rem;">Preset Cepat:</span>
                                    <button type="button" class="preset-btn" onclick="setPreset('today')">Hari Ini</button>
                                    <button type="button" class="preset-btn" onclick="setPreset('last7')">7 Hari Terakhir</button>
                                    <button type="button" class="preset-btn" onclick="setPreset('this_month')">Bulan Ini</button>
                                    <button type="button" class="preset-btn" onclick="setPreset('last_month')">Bulan Lalu</button>
                                    <button type="button" class="preset-btn" onclick="setPreset('this_year')">Tahun Ini</button>
                                </div>
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    <a href="{{ route('admin.reports.sales') }}" class="btn btn--secondary btn--sm">
                                        Reset
                                    </a>
                                    <button type="submit" class="btn btn--primary btn--sm">
                                        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                        </svg>
                                        Tampilkan Laporan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- 6 Summary Cards (Indikator Kinerja Penjualan & Keuntungan) --}}
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem;">
                
                {{-- 1. Total Penjualan (Omset) --}}
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--green" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Total Penjualan (Omset)</p>
                        <p class="stat-card__value" style="font-size:var(--text-xl); color:var(--color-success);">
                            Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}
                        </p>
                        <span style="font-size:var(--text-xs); color:var(--gray-500);">Gross Revenue</span>
                    </div>
                </div>

                {{-- 2. Total Modal / HPP --}}
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--blue" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Total Modal (HPP)</p>
                        <p class="stat-card__value" style="font-size:var(--text-xl);">
                            Rp {{ number_format($report['total_cogs'], 0, ',', '.') }}
                        </p>
                        <span style="font-size:var(--text-xs); color:var(--gray-500);">Harga Pokok Penjualan</span>
                    </div>
                </div>

                {{-- 3. Total Keuntungan (Laba Bersih Penjualan) --}}
                <div class="stat-card" style="border: 2px solid var(--color-primary-light); background: linear-gradient(135deg, var(--surface-white) 0%, var(--color-primary-bg) 100%);">
                    <div class="stat-card__icon stat-card__icon--indigo" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label font-bold" style="color:var(--color-primary);">Total Keuntungan (Laba)</p>
                        <p class="stat-card__value" style="font-size:var(--text-2xl); color:var(--color-primary);">
                            Rp {{ number_format($report['total_profit'], 0, ',', '.') }}
                        </p>
                        <span class="badge badge--indigo" style="margin-top:0.25rem;">
                            Margin: {{ number_format($report['profit_margin'], 2, ',', '.') }}%
                        </span>
                    </div>
                </div>

                {{-- 4. Margin Keuntungan % --}}
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--yellow" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Persentase Margin</p>
                        <p class="stat-card__value" style="font-size:var(--text-2xl);">
                            {{ number_format($report['profit_margin'], 2, ',', '.') }}%
                        </p>
                        <span style="font-size:var(--text-xs); color:var(--gray-500);">Rasio Laba / Penjualan</span>
                    </div>
                </div>

                {{-- 5. Jumlah Transaksi & AOV --}}
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--blue" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Jumlah Transaksi</p>
                        <p class="stat-card__value">
                            {{ number_format($report['transaction_count']) }}
                        </p>
                        <span style="font-size:var(--text-xs); color:var(--gray-500);">Rata-rata: Rp {{ number_format($report['average_order_value'], 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- 6. Total Barang Terjual --}}
                <div class="stat-card">
                    <div class="stat-card__icon stat-card__icon--indigo" aria-hidden="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="stat-card__label">Total Item Terjual</p>
                        <p class="stat-card__value">
                            {{ number_format($report['total_items_sold']) }} <span style="font-size:var(--text-sm); font-weight:normal; color:var(--gray-500);">unit</span>
                        </p>
                        <span style="font-size:var(--text-xs); color:var(--gray-500);">Kuantitas Produk Keluar</span>
                    </div>
                </div>

            </div>

            {{-- Grafik Tren Penjualan & Keuntungan Harian (Monitoring Chart) --}}
            <div class="card chart-wrapper">
                <div class="card__header flex items-center justify-between">
                    <span class="font-bold">Grafik Tren Penjualan &amp; Keuntungan Harian</span>
                    <span style="font-size:var(--text-xs); color:var(--gray-500);">
                        Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                    </span>
                </div>
                <div class="card__body">
                    <div class="chart-container" style="height:320px;">
                        <canvas id="salesProfitChart" aria-label="Grafik Penjualan dan Keuntungan" role="img"></canvas>
                    </div>
                </div>
            </div>

            {{-- Tabel 1: Top 10 Produk Terlaris & Kontribusi Keuntungan --}}
            <div class="table-wrapper">
                <div class="card__header flex items-center justify-between">
                    <div>
                        <span class="font-bold">10 Produk Terlaris &amp; Kontribusi Keuntungan</span>
                        <p style="font-size:var(--text-xs); color:var(--gray-500); margin:2px 0 0 0;">
                            Peringkat produk berdasarkan kuantitas penjualan dan kontribusi keuntungan
                        </p>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:3rem">No.</th>
                                <th>Produk</th>
                                <th>SKU</th>
                                <th>Kategori</th>
                                <th class="text-center">Qty Terjual</th>
                                <th class="text-right">Total Penjualan</th>
                                <th class="text-right">Total Modal (HPP)</th>
                                <th class="text-right">Keuntungan</th>
                                <th class="text-center">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['top_products'] as $index => $product)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="font-semibold">{{ $product->product_name }}</td>
                                    <td class="font-mono">{{ $product->sku }}</td>
                                    <td>{{ $product->category_name ?? '-' }}</td>
                                    <td class="text-center font-bold">{{ number_format($product->total_qty) }}</td>
                                    <td class="text-right">Rp {{ number_format((float) $product->total_revenue, 0, ',', '.') }}</td>
                                    <td class="text-right" style="color:var(--gray-500);">Rp {{ number_format((float) $product->total_cogs, 0, ',', '.') }}</td>
                                    <td class="text-right font-bold" style="color:var(--color-primary);">
                                        Rp {{ number_format((float) $product->total_profit, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $product->profit_margin >= 20 ? 'badge--green' : ($product->profit_margin >= 10 ? 'badge--indigo' : 'badge--yellow') }}">
                                            {{ number_format((float) $product->profit_margin, 1, ',', '.') }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table__empty">
                                    <td colspan="9">Tidak ada data penjualan pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabel 2: Rincian Seluruh Transaksi Penjualan --}}
            <div class="table-wrapper">
                <div class="card__header flex items-center justify-between">
                    <div>
                        <span class="font-bold">Rincian Riwayat Transaksi Penjualan</span>
                        <p style="font-size:var(--text-xs); color:var(--gray-500); margin:2px 0 0 0;">
                            Total {{ number_format($report['transactions']->count()) }} transaksi tercatat
                        </p>
                    </div>
                </div>
                <div class="table-scroll">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:3rem">No.</th>
                                <th>No. Faktur</th>
                                <th>Waktu Transaksi</th>
                                <th>Kasir</th>
                                <th class="text-center">Item</th>
                                <th class="text-right">Total Penjualan</th>
                                <th class="text-right">Modal (HPP)</th>
                                <th class="text-right">Keuntungan</th>
                                <th class="text-center">Margin</th>
                                <th class="text-right no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($report['transactions'] as $index => $trx)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td class="font-mono font-semibold">{{ $trx->invoice_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($trx->sold_at)->translatedFormat('d M Y, H:i') }}</td>
                                    <td>{{ $trx->cashier_name ?? '-' }}</td>
                                    <td class="text-center">{{ number_format($trx->total_items) }}</td>
                                    <td class="text-right font-semibold">
                                        Rp {{ number_format((float) $trx->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right" style="color:var(--gray-500);">
                                        Rp {{ number_format((float) $trx->total_cogs, 0, ',', '.') }}
                                    </td>
                                    <td class="text-right font-bold" style="color:var(--color-primary);">
                                        Rp {{ number_format((float) $trx->profit, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $trx->profit_margin >= 20 ? 'badge--green' : ($trx->profit_margin >= 10 ? 'badge--indigo' : 'badge--yellow') }}">
                                            {{ number_format((float) $trx->profit_margin, 1, ',', '.') }}%
                                        </span>
                                    </td>
                                    <td class="text-right no-print">
                                        <div class="table__actions">
                                            <a href="{{ route('admin.sales.show', $trx->id) }}" class="btn-link">Detail</a>
                                            <a href="{{ route('admin.sales.invoice', $trx->id) }}" class="btn-link--success">Faktur</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table__empty">
                                    <td colspan="10">Tidak ada transaksi penjualan pada rentang tanggal yang dipilih.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($report['transactions']->isNotEmpty())
                            <tfoot>
                                <tr style="background-color:var(--gray-50); border-top:2px solid var(--gray-300); font-weight:bold;">
                                    <td colspan="4" class="text-right">TOTAL KESELURUHAN:</td>
                                    <td class="text-center">{{ number_format($report['total_items_sold']) }}</td>
                                    <td class="text-right" style="color:var(--color-success);">
                                        Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-right" style="color:var(--gray-600);">
                                        Rp {{ number_format($report['total_cogs'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-right" style="color:var(--color-primary);">
                                        Rp {{ number_format($report['total_profit'], 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($report['profit_margin'], 2, ',', '.') }}%
                                    </td>
                                    <td class="no-print"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Preset Cepat Periode
        function setPreset(type) {
            const today = new Date();
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');

            function formatDate(d) {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            if (type === 'today') {
                const nowStr = formatDate(today);
                startInput.value = nowStr;
                endInput.value = nowStr;
            } else if (type === 'last7') {
                const past7 = new Date();
                past7.setDate(today.getDate() - 6);
                startInput.value = formatDate(past7);
                endInput.value = formatDate(today);
            } else if (type === 'this_month') {
                const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
                startInput.value = formatDate(firstDay);
                endInput.value = formatDate(today);
            } else if (type === 'last_month') {
                const firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                startInput.value = formatDate(firstDayLastMonth);
                endInput.value = formatDate(lastDayLastMonth);
            } else if (type === 'this_year') {
                const firstDayYear = new Date(today.getFullYear(), 0, 1);
                startInput.value = formatDate(firstDayYear);
                endInput.value = formatDate(today);
            }

            document.getElementById('filterForm').submit();
        }

        // Render Grafik Monitoring Tren Penjualan & Keuntungan
        (function () {
            const chartData = @json($report['chart_data']);
            const ctx = document.getElementById('salesProfitChart');

            if (!ctx) return;

            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Penjualan (Omset)',
                            data: chartData.revenues,
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0.08)',
                            borderWidth: 2.5,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                        },
                        {
                            label: 'Keuntungan (Laba Bersih)',
                            data: chartData.profits,
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.15)',
                            borderWidth: 2.5,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 3,
                            pointHoverRadius: 6,
                        }
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: {
                                    family: 'inherit',
                                    size: 12,
                                    weight: 600,
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed.y || 0;
                                    return context.dataset.label + ': Rp ' + val.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                maxRotation: 45,
                                autoSkip: true,
                                maxTicksLimit: 15,
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(229, 231, 235, 0.7)' },
                            ticks: {
                                callback: val => 'Rp ' + (val >= 1000000 ? (val / 1000000).toFixed(1) + ' jt' : val.toLocaleString('id-ID')),
                            }
                        }
                    }
                }
            });
        })();
    </script>
    @endpush
</x-app-layout>
