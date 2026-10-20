<x-app-layout>
    <x-slot name="breadcrumbs">
        <x-breadcrumb :links="[
            ['label' => 'Manajemen Pengguna', 'url' => route('admin.users.index')],
            ['label' => 'Edit Pengguna', 'url' => null],
        ]" />
    </x-slot>

    <x-slot name="header">
        <h2 class="navbar__page-title">Edit Pengguna</h2>
    </x-slot>

    <div class="page-body">
        <div style="max-width:640px">
            <div class="card">
                <div class="card__header">
                    Edit Pengguna
                    <p class="text-sm text-gray-500" style="font-weight:400;margin-top:2px">
                        Memperbarui data akun: <strong>{{ $user->email }}</strong>
                    </p>
                </div>
                <div class="card__body">

                    <form method="POST" action="{{ route('admin.users.update', $user) }}" novalidate
                          x-data="{ loading: false }"
                          @submit="loading = true">
                        @csrf
                        @method('PUT')

                        {{-- Nama --}}
                        <div class="form-group">
                            <label for="name" class="form-label form-label--required">Nama Lengkap</label>
                            <input id="name"
                                   type="text"
                                   name="name"
                                   value="{{ old('name', $user->name) }}"
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
                                   value="{{ old('email', $user->email) }}"
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
                                <option value="admin"  {{ old('role', $user->role) === 'admin'  ? 'selected' : '' }}>Admin</option>
                                <option value="kasir"  {{ old('role', $user->role) === 'kasir'  ? 'selected' : '' }}>Kasir</option>
                                <option value="gudang" {{ old('role', $user->role) === 'gudang' ? 'selected' : '' }}>Gudang</option>
                            </select>
                            <x-form-error field="role" />
                        </div>

                        {{-- Status Aktif --}}
                        <div class="form-group">
                            <fieldset>
                                <legend class="form-label">Status Akun</legend>
                                <label class="form-check">
                                    <input id="is_active"
                                           type="checkbox"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                           {{ $user->id === auth()->id() ? 'disabled' : '' }}
                                           class="form-check__input">
                                    <span class="form-check__label">Akun aktif</span>
                                </label>
                                @if ($user->id === auth()->id())
                                    <p class="form-hint">Anda tidak dapat menonaktifkan akun Anda sendiri.</p>
                                @endif
                            </fieldset>
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.users.index') }}" class="btn btn--secondary">Batal</a>
                            <button type="submit"
                                    :disabled="loading"
                                    class="btn btn--primary">
                                <span x-show="!loading">Simpan Perubahan</span>
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
