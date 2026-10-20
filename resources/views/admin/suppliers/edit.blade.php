<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Manajemen Pemasok', 'url' => route('admin.suppliers.index')],
            ['label' => 'Edit Pemasok', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Edit Pemasok</h2>
    </x-slot>

    <div class="page-body">
        <div class="card">
            <div class="card__header">Edit Pemasok: {{ $supplier->name }}</div>
            <div class="card__body">
                <form method="POST"
                      action="{{ route('admin.suppliers.update', $supplier) }}"
                      novalidate
                      x-data="{ loading: false }"
                      @submit="loading = true">
                    @csrf
                    @method('PUT')

                    {{-- Nama Pemasok --}}
                    <div class="form-group">
                        <label for="name" class="form-label">
                            Nama Pemasok <span style="color:var(--color-error);" aria-hidden="true">*</span>
                        </label>
                        <input id="name"
                               type="text"
                               name="name"
                               value="{{ old('name', $supplier->name) }}"
                               required
                               maxlength="100"
                               autocomplete="off"
                               aria-describedby="{{ $errors->has('name') ? 'name-error' : 'name-hint' }}"
                               aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                               class="form-input @error('name') form-input--error @enderror">
                        <p id="name-hint" class="form-hint">Maksimal 100 karakter. Nama harus unik.</p>
                        <x-form-error field="name" />
                    </div>

                    {{-- Nomor Telepon --}}
                    <div class="form-group">
                        <label for="phone" class="form-label">
                            Nomor Telepon
                            <span style="color:var(--gray-400); font-weight:400;">(opsional)</span>
                        </label>
                        <input id="phone"
                               type="text"
                               name="phone"
                               value="{{ old('phone', $supplier->phone) }}"
                               maxlength="15"
                               autocomplete="off"
                               inputmode="numeric"
                               aria-describedby="{{ $errors->has('phone') ? 'phone-error' : 'phone-hint' }}"
                               aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
                               class="form-input @error('phone') form-input--error @enderror">
                        <p id="phone-hint" class="form-hint">Hanya angka, 7 hingga 15 digit.</p>
                        <x-form-error field="phone" />
                    </div>

                    {{-- Email --}}
                    <div class="form-group">
                        <label for="email" class="form-label">
                            Email
                            <span style="color:var(--gray-400); font-weight:400;">(opsional)</span>
                        </label>
                        <input id="email"
                               type="email"
                               name="email"
                               value="{{ old('email', $supplier->email) }}"
                               maxlength="255"
                               autocomplete="off"
                               aria-describedby="email-error"
                               aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                               class="form-input @error('email') form-input--error @enderror">
                        <x-form-error field="email" />
                    </div>

                    {{-- Alamat --}}
                    <div class="form-group">
                        <label for="address" class="form-label">
                            Alamat
                            <span style="color:var(--gray-400); font-weight:400;">(opsional)</span>
                        </label>
                        <textarea id="address"
                                  name="address"
                                  rows="3"
                                  maxlength="500"
                                  aria-describedby="{{ $errors->has('address') ? 'address-error' : 'address-hint' }}"
                                  aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}"
                                  class="form-textarea @error('address') form-textarea--error @enderror">{{ old('address', $supplier->address) }}</textarea>
                        <p id="address-hint" class="form-hint">Maksimal 500 karakter.</p>
                        <x-form-error field="address" />
                    </div>

                    {{-- Tombol Aksi --}}
                    <div style="display:flex; gap:0.75rem; justify-content:flex-end; margin-top:1.5rem;">
                        <a href="{{ route('admin.suppliers.index') }}" class="btn btn--secondary">Batal</a>
                        <button type="submit"
                                :disabled="loading"
                                class="btn btn--primary">
                            <span x-show="!loading">Perbarui Pemasok</span>
                            <span x-show="loading" style="display:flex; align-items:center; gap:0.5rem;">
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
