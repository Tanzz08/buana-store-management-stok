<form method="post" action="{{ route('password.update') }}" class="space-y-4">
    @csrf
    @method('put')

    <div class="form-group">
        <label for="update_password_current_password" class="form-label">Kata Sandi Saat Ini</label>
        <input
            id="update_password_current_password"
            name="current_password"
            type="password"
            class="form-input {{ $errors->updatePassword->has('current_password') ? 'form-input--error' : '' }}"
            autocomplete="current-password"
            aria-describedby="{{ $errors->updatePassword->has('current_password') ? 'current-password-error' : '' }}"
        >
        @if ($errors->updatePassword->has('current_password'))
            <p id="current-password-error" class="form-error" aria-live="polite">{{ $errors->updatePassword->first('current_password') }}</p>
        @endif
    </div>

    <div class="form-group">
        <label for="update_password_password" class="form-label">Kata Sandi Baru</label>
        <input
            id="update_password_password"
            name="password"
            type="password"
            class="form-input {{ $errors->updatePassword->has('password') ? 'form-input--error' : '' }}"
            autocomplete="new-password"
            aria-describedby="{{ $errors->updatePassword->has('password') ? 'new-password-error' : '' }}"
        >
        @if ($errors->updatePassword->has('password'))
            <p id="new-password-error" class="form-error" aria-live="polite">{{ $errors->updatePassword->first('password') }}</p>
        @endif
    </div>

    <div class="form-group">
        <label for="update_password_password_confirmation" class="form-label">Konfirmasi Kata Sandi Baru</label>
        <input
            id="update_password_password_confirmation"
            name="password_confirmation"
            type="password"
            class="form-input {{ $errors->updatePassword->has('password_confirmation') ? 'form-input--error' : '' }}"
            autocomplete="new-password"
        >
        @if ($errors->updatePassword->has('password_confirmation'))
            <p class="form-error" aria-live="polite">{{ $errors->updatePassword->first('password_confirmation') }}</p>
        @endif
    </div>

    <div class="flex items-center gap-4">
        <button type="submit" class="btn btn--primary">Simpan</button>

        @if (session('status') === 'password-updated')
            <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 3000)"
                class="text-sm text-gray-500"
                aria-live="polite"
                x-cloak
            >Kata sandi diperbarui.</p>
        @endif
    </div>
</form>
