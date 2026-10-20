<x-guest-layout>
    <h2 class="guest-title">Lupa Kata Sandi</h2>
    <p class="guest-subtitle">
        Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
    </p>

    {{-- Status / Pesan Konfirmasi --}}
    @if (session('status'))
        <div class="alert alert--success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" novalidate>
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

        <div style="margin-top:var(--space-5);">
            <x-primary-button style="width:100%; justify-content:center;">
                Kirim Tautan Reset
            </x-primary-button>
        </div>
    </form>

    <div style="margin-top:var(--space-5); text-align:center;">
        <a href="{{ route('login') }}" class="btn-link">
            Kembali ke halaman login
        </a>
    </div>
</x-guest-layout>
