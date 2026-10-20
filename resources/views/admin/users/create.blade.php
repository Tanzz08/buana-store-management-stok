<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Manajemen Pengguna', 'url' => route('admin.users.index')],
            ['label' => 'Tambah Pengguna', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Tambah Pengguna</h2>
    </x-slot>

    <div class="page-body">
        <div style="max-width:640px">
            <div class="card">
                <div class="card__header">Form Tambah Pengguna Baru</div>
                <div class="card__body">

                    <form method="POST" action="{{ route('admin.users.store') }}" novalidate
                          x-data="{ loading: false }"
                          @submit="loading = true">
                        @csrf

                        {{-- Nama --}}
                        <div class="form-group">
                            <label for="name" class="form-label form-label--required">Nama Lengkap</label>
                            <input id="name"
                                   type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   maxlength="255"
                                   autocomplete="name"
                                   aria-describedby="name-error"
                                   aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                                   class="form-input {{ $errors->has('name') ? 'form-input--error' : '' }}">
                            <x-form-error field="name" />
                        </div>

                        {{-- Email --}}
                        <div class="form-group">
                            <label for="email" class="form-label form-label--required">Alamat Email</label>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   maxlength="255"
                                   autocomplete="email"
                                   aria-describedby="email-error"
                                   aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                   class="form-input {{ $errors->has('email') ? 'form-input--error' : '' }}">
                            <x-form-error field="email" />
                        </div>

                        {{-- Peran --}}
                        <div class="form-group">
                            <label for="role" class="form-label form-label--required">Peran</label>
                            <select id="role"
                                    name="role"
                                    required
                                    aria-describedby="role-error"
                                    aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                                    class="form-select {{ $errors->has('role') ? 'form-select--error' : '' }}">
                                <option value="">-- Pilih Peran --</option>
                                <option value="admin"  {{ old('role') === 'admin'  ? 'selected' : '' }}>Admin</option>
                                <option value="kasir"  {{ old('role') === 'kasir'  ? 'selected' : '' }}>Kasir</option>
                                <option value="gudang" {{ old('role') === 'gudang' ? 'selected' : '' }}>Gudang</option>
                            </select>
                            <x-form-error field="role" />
                        </div>

                        {{-- Kata Sandi --}}
                        <div class="form-group">
                            <label for="password" class="form-label form-label--required">Kata Sandi</label>
                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   minlength="8"
                                   autocomplete="new-password"
                                   aria-describedby="password-hint password-error"
                                   aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                                   class="form-input {{ $errors->has('password') ? 'form-input--error' : '' }}">
                            <p id="password-hint" class="form-hint">Minimal 8 karakter.</p>
                            <x-form-error field="password" />
                        </div>

                        {{-- Konfirmasi Kata Sandi --}}
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label form-label--required">Konfirmasi Kata Sandi</label>
                            <input id="password_confirmation"
                                   type="password"
                                   name="password_confirmation"
                                   required
                                   autocomplete="new-password"
                                   aria-describedby="password_confirmation-error"
                                   aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}"
                                   class="form-input {{ $errors->has('password_confirmation') ? 'form-input--error' : '' }}">
                            <x-form-error field="password_confirmation" />
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">Batal</a>
                            <button type="submit"
                                    :disabled="loading"
                                    class="btn btn--primary">
                                <span x-show="!loading">Simpan Pengguna</span>
                                <span x-show="loading" class="flex items-center gap-2" x-cloak>
                                    <svg style="width:16px;height:16px;animation:spin 1s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle style="opacity:0.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path style="opacity:0.75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    Menyimpan...
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
