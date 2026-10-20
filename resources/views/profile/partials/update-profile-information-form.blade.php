<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf
    @method('patch')

    <div class="form-group">
        <label for="name" class="form-label">Nama</label>
        <input
            id="name"
            name="name"
            type="text"
            class="form-input {{ $errors->has('name') ? 'form-input--error' : '' }}"
            value="{{ old('name', $user->name) }}"
            required
            autofocus
            autocomplete="name"
            aria-describedby="{{ $errors->has('name') ? 'name-error' : '' }}"
        >
        @error('name')
            <p id="name-error" class="form-error" aria-live="polite">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            class="form-input {{ $errors->has('email') ? 'form-input--error' : '' }}"
            value="{{ old('email', $user->email) }}"
            required
            autocomplete="username"
            aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
        >
        @error('email')
            <p id="email-error" class="form-error" aria-live="polite">{{ $message }}</p>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2">
                <p class="text-sm text-gray-500">
                    Alamat email Anda belum diverifikasi.
                    <button form="send-verification" class="btn-link">
                        Klik di sini untuk mengirim ulang email verifikasi.
                    </button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert--success mt-2" role="alert">
                        <p>Tautan verifikasi baru telah dikirim ke alamat email Anda.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="btn btn--primary">Simpan</button>

        @if (session('status') === 'profile-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 3000)"
                class="text-sm text-gray-500"
                aria-live="polite"
                x-cloak
            >Perubahan disimpan.</p>
        @endif
    </div>
</form>
