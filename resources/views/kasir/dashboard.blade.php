<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Dasbor', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Dasbor Kasir</h2>
    </x-slot>

    <div class="page-body">
        <div class="card">
            <div class="card__body">
                <p>Selamat datang, <strong>{{ auth()->user()->name }}</strong>!</p>
                <p style="margin-top:0.25rem; font-size:var(--text-sm); color:var(--color-text-muted);">Anda masuk sebagai <span style="font-weight:600;">Kasir</span>.</p>
            </div>
        </div>
    </div>
</x-app-layout>
