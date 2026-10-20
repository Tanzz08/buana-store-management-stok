<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Manajemen Kategori', 'url' => route('admin.categories.index')],
            ['label' => 'Tambah Kategori', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Tambah Kategori</h2>
    </x-slot>

    <div class="page-body">
        <div class="card">
            <div class="card__header">Form Tambah Kategori Baru</div>
            <div class="card__body">
                <form method="POST" action="{{ route('admin.categories.store') }}" novalidate
                      x-data="{ loading: false }"
                      @submit="loading = true">
                    @csrf

                    {{-- Nama Kategori --}}
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Nama Kategori <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="name"
                               type="text"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               maxlength="100"
                               autocomplete="off"
                               aria-describedby="{{ $errors->has('name') ? 'name-error' : 'name-hint' }}"
                               aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                               class="form-input @error('name') form-input--error @enderror">
                        <p id="name-hint" class="form-hint">Maksimal 100 karakter. Nama harus unik.</p>
                        <x-form-error field="name" />
                    </div>

                    {{-- Deskripsi --}}
                    <div class="form-group">
                        <label for="description" class="form-label">
                            Deskripsi
                            <span style="color:var(--gray-400); font-weight:400;">(opsional)</span>
                        </label>
                        <textarea id="description"
                                  name="description"
                                  rows="4"
                                  maxlength="500"
                                  aria-describedby="{{ $errors->has('description') ? 'description-error' : 'description-hint' }}"
                                  aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                                  class="form-textarea @error('description') form-textarea--error @enderror">{{ old('description') }}</textarea>
                        <p id="description-hint" class="form-hint">Maksimal 500 karakter.</p>
                        <x-form-error field="description" />
                    </div>

                    {{-- Tombol Aksi --}}
                    <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem;">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn--secondary">Batal</a>
                        <button type="submit"
                                :disabled="loading"
                                class="btn btn--primary"
                                aria-busy="loading">
                            <span x-show="!loading">Simpan Kategori</span>
                            <span x-show="loading" style="display:flex; align-items:center; gap:0.5rem;" aria-live="polite">
                                <svg style="width:1rem;height:1rem;animation:spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle style="opacity:0.25;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path style="opacity:0.75;" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
