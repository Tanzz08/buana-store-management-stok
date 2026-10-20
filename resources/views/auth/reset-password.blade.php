<x-guest-layout>
    <h2 class="guest-title">Atur Ulang Kata Sandi</h2>
    <p class="guest-subtitle">Buat kata sandi baru untuk akun Anda</p>

    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf

        {{-- Token --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input id="email"
                   class="form-input {{ $errors->has('email') ? 'form-input--error' : '' }}"
                   type="email"
                   name="email"
                   value="{{ old('email', $request->email) }}"
                   required
                   autofocus
                   autocomplete="username"
                   {{ $errors->has('email') ? 'aria-describedby=email-error aria-invalid=true' : '' }}>
            @error('email')
                <p id="email-error" class="form-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        {{-- Kata Sandi Baru --}}
        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi Baru</label>
            <input id="password"
                   class="form-input {{ $errors->has('password') ? 'form-input--error' : '' }}"
                   type="password"
                   name="password"
                   required
                   autocomplete="new-password"
                   {{ $errors->has('password') ? 'aria-describedby=password-error aria-invalid=true' : '' }}>
            @error('password')
                <p id="password-error" class="form-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        {{-- Konfirmasi Kata Sandi Baru --}}
        <div class="form-group">
            <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
            <input id="password_confirmation"
                   class="form-input {{ $errors->has('password_confirmation') ? 'form-input--error' : '' }}"
                   type="password"
                   name="password_confirmation"
                   required
                   autocomplete="new-password"
                   {{ $errors->has('password_confirmation') ? 'aria-describedby=password-confirm-error aria-invalid=true' : '' }}>
            @error('password_confirmation')
                <p id="password-confirm-error" class="form-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-top:var(--space-6);">
            <x-primary-button style="width:100%; justify-content:center;">
                Simpan Kata Sandi Baru
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
