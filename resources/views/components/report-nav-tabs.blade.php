<div class="report-tabs-container no-print" style="margin-bottom: 1.5rem;">
    <div style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--gray-200); padding-bottom: 0.25rem; overflow-x: auto;">
        <a href="{{ route('admin.reports.sales') }}"
           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; font-size: var(--text-sm); font-weight: 600; border-radius: var(--radius-md) var(--radius-md) 0 0; text-decoration: none; border-bottom: 3px solid {{ request()->routeIs('admin.reports.sales') ? 'var(--color-primary)' : 'transparent' }}; color: {{ request()->routeIs('admin.reports.sales') ? 'var(--color-primary)' : 'var(--gray-600)' }}; background-color: {{ request()->routeIs('admin.reports.sales') ? 'var(--color-primary-bg)' : 'transparent' }}; transition: all var(--transition-fast);">
            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Laporan Penjualan &amp; Keuntungan
        </a>

        <a href="{{ route('admin.reports.inventory') }}"
           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; font-size: var(--text-sm); font-weight: 600; border-radius: var(--radius-md) var(--radius-md) 0 0; text-decoration: none; border-bottom: 3px solid {{ request()->routeIs('admin.reports.inventory') ? 'var(--color-primary)' : 'transparent' }}; color: {{ request()->routeIs('admin.reports.inventory') ? 'var(--color-primary)' : 'var(--gray-600)' }}; background-color: {{ request()->routeIs('admin.reports.inventory') ? 'var(--color-primary-bg)' : 'transparent' }}; transition: all var(--transition-fast);">
            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
            </svg>
            Laporan Nilai Inventaris
        </a>

        <a href="{{ route('admin.reports.stock-mutation') }}"
           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; font-size: var(--text-sm); font-weight: 600; border-radius: var(--radius-md) var(--radius-md) 0 0; text-decoration: none; border-bottom: 3px solid {{ request()->routeIs('admin.reports.stock-mutation') ? 'var(--color-primary)' : 'transparent' }}; color: {{ request()->routeIs('admin.reports.stock-mutation') ? 'var(--color-primary)' : 'var(--gray-600)' }}; background-color: {{ request()->routeIs('admin.reports.stock-mutation') ? 'var(--color-primary-bg)' : 'transparent' }}; transition: all var(--transition-fast);">
            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            Laporan Mutasi Stok
        </a>
    </div>
</div>
