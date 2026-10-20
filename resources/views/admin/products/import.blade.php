<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Manajemen Produk', 'url' => route('admin.products.index')],
            ['label' => 'Impor CSV', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Impor Produk dari CSV</h2>
    </x-slot>

    <div class="page-body">

        {{-- Notifikasi hasil impor sebelumnya --}}
        @if (session('import_error'))
            <div class="alert alert--error" role="alert">
                <svg class="alert__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                          clip-rule="evenodd" />
                </svg>
                <div class="alert__body">{{ session('import_error') }}</div>
            </div>
        @endif

        @if (session('import_success') !== null)
            <div class="alert alert--success" role="status">
                <svg class="alert__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                          d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z"
                          clip-rule="evenodd" />
                </svg>
                <div class="alert__body">
                    <strong>{{ session('import_success') }} produk</strong> berhasil diimpor.
                    @if (session('import_failures') && count(session('import_failures')) > 0)
                        {{ count(session('import_failures')) }} baris gagal — lihat detail di bawah.
                    @endif
                </div>
            </div>
        @endif

        {{-- Form upload --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header">Unggah File CSV</div>
            <div class="card__body">
                <p style="font-size:var(--text-sm); color:var(--gray-500); margin-bottom:1.5rem;">
                    Unggah file CSV dengan kolom wajib berikut (baris pertama = header):
                    <code style="background:var(--gray-100); padding:0.125rem 0.25rem; border-radius:var(--radius-sm); font-size:var(--text-xs); font-family:var(--font-mono);">
                        sku, nama, kategori, harga_beli, harga_jual, satuan, stok_minimum
                    </code>
                </p>

                @if ($errors->any())
                    <div class="alert alert--error" role="alert" id="file-upload-errors" style="margin-bottom:1rem;">
                        <div class="alert__body">
                            <ul style="list-style:disc; padding-left:1.25rem; margin:0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('admin.products.import') }}"
                      enctype="multipart/form-data"
                      x-data="{ loading: false }"
                      @submit="loading = true"
                      novalidate>
                    @csrf

                    <div class="form-group">
                        <label for="csv_file" class="form-label">
                            File CSV <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="csv_file"
                               type="file"
                               name="csv_file"
                               accept=".csv,.txt"
                               required
                               aria-describedby="{{ $errors->has('csv_file') ? 'csv-file-error' : 'csv-file-hint' }}"
                               class="form-input @error('csv_file') form-input--error @enderror">
                        <p id="csv-file-hint" class="form-hint">Format: .csv atau .txt, maks. 2 MB.</p>
                        @error('csv_file')
                            <p id="csv-file-error" class="form-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div style="display:flex; align-items:center; justify-content:space-between; gap:0.75rem; margin-top:1.5rem;">
                        <a href="{{ route('admin.products.index') }}" class="btn btn--secondary">
                            ← Kembali ke Daftar Produk
                        </a>
                        <button type="submit"
                                :disabled="loading"
                                class="btn btn--primary">
                            <svg x-show="loading"
                                 style="width:1rem;height:1rem;animation:spin 1s linear infinite;"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 aria-hidden="true">
                                <circle style="opacity:0.25;" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                <path style="opacity:0.75;" fill="currentColor"
                                      d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span x-text="loading ? 'Memproses...' : 'Impor Sekarang'">Impor Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Panduan format CSV --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header">Panduan Format CSV</div>
            <div class="card__body">
                <div class="table-scroll">
                    <table class="table" aria-label="Penjelasan kolom CSV">
                        <thead>
                            <tr>
                                <th>Kolom</th>
                                <th>Tipe</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-mono">sku</td>
                                <td>Teks</td>
                                <td>Kode unik produk, maks. 100 karakter. Harus belum terdaftar.</td>
                            </tr>
                            <tr>
                                <td class="font-mono">nama</td>
                                <td>Teks</td>
                                <td>Nama produk, maks. 255 karakter.</td>
                            </tr>
                            <tr>
                                <td class="font-mono">kategori</td>
                                <td>Teks</td>
                                <td>Nama kategori yang sudah terdaftar di sistem (tidak peka huruf besar/kecil).</td>
                            </tr>
                            <tr>
                                <td class="font-mono">harga_beli</td>
                                <td>Angka</td>
                                <td>Harga beli per unit. Harus lebih dari 0. Gunakan titik sebagai pemisah desimal.</td>
                            </tr>
                            <tr>
                                <td class="font-mono">harga_jual</td>
                                <td>Angka</td>
                                <td>Harga jual per unit. Harus lebih dari 0. Gunakan titik sebagai pemisah desimal.</td>
                            </tr>
                            <tr>
                                <td class="font-mono">satuan</td>
                                <td>Teks</td>
                                <td>Satuan produk (contoh: unit, pcs, buah), maks. 50 karakter.</td>
                            </tr>
                            <tr>
                                <td class="font-mono">stok_minimum</td>
                                <td>Bilangan bulat</td>
                                <td>Batas stok minimum untuk notifikasi. Minimal 1.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="form-hint" style="margin-top:0.75rem;">
                    Contoh baris: <code style="background:var(--gray-100); padding:0.125rem 0.25rem; border-radius:var(--radius-sm); font-family:var(--font-mono);">SKU001,Samsung Galaxy A15,Handphone,1500000,2000000,unit,5</code>
                </p>
            </div>
        </div>

        {{-- Tabel baris gagal --}}
        @if (session('import_failures') && count(session('import_failures')) > 0)
            <div class="card">
                <div class="card__header" style="color:var(--color-error);" id="failures-heading">
                    Baris Gagal Diimpor ({{ count(session('import_failures')) }} baris)
                </div>
                <div class="card__body">
                    <div class="table-scroll" aria-labelledby="failures-heading">
                        <table class="table" role="table">
                            <thead>
                                <tr>
                                    <th>Baris</th>
                                    <th>SKU</th>
                                    <th>Alasan Kegagalan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (session('import_failures') as $failure)
                                    <tr>
                                        <td>{{ $failure['row'] }}</td>
                                        <td class="font-mono">{{ $failure['sku'] !== '' ? $failure['sku'] : '—' }}</td>
                                        <td style="color:var(--color-error);">{{ $failure['reason'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
