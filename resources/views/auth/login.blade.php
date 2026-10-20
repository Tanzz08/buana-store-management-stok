<x-guest-layout>
    {{-- Status session (misalnya setelah reset password berhasil) --}}
    <x-auth-session-status :status="session('status')" />

    <h2 class="guest-title">Masuk ke Sistem</h2>
    <p class="guest-subtitle">Masukkan kredensial Anda untuk mengakses sistem</p>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input id="email"
                   class="form-input {{ $errors->has('email') ? 'form-input--error' : '' }}"
                   type="email"
                   name="email"
                   value="{{ old('email') }}"
                   required
                   autofocus
                   autocomplete="username"
                   {{ $errors->has('email') ? 'aria-describedby=email-error aria-invalid=true' : '' }}>
            @error('email')
                <p id="email-error" class="form-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        {{-- Kata Sandi --}}
        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi</label>
            <input id="password"
                   class="form-input {{ $errors->has('password') ? 'form-input--error' : '' }}"
                   type="password"
                   name="password"
                   required
                   autocomplete="current-password"
                   {{ $errors->has('password') ? 'aria-describedby=password-error aria-invalid=true' : '' }}>
            @error('password')
                <p id="password-error" class="form-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        {{-- Ingat Saya --}}
        <div class="form-group">
            <label class="form-check" for="remember_me">
                <input id="remember_me"
                       class="form-check__input"
                       type="checkbox"
                       name="remember">
                <span class="form-check__label">Ingat saya</span>
            </label>
        </div>

        {{-- Lupa Kata Sandi + Tombol Masuk --}}
        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:var(--space-6);">
            @if (Route::has('password.request'))
                <a class="btn-link"
                   href="{{ route('password.request') }}">
                    Lupa kata sandi?
                </a>
            @endif

            <x-primary-button>
                Masuk
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
