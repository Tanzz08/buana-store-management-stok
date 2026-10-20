<div x-data="{ showDeleteModal: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">
    <button
        type="button"
        class="btn btn--danger"
        @click="showDeleteModal = true"
        aria-haspopup="dialog"
    >
        Hapus Akun
    </button>

    {{-- Delete Confirmation Modal --}}
    <div
        class="modal-backdrop"
        :class="showDeleteModal ? 'is-open' : ''"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-modal-title"
        aria-describedby="delete-modal-desc"
        @keydown.escape.window="showDeleteModal = false"
        x-show="showDeleteModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
    >
        <div class="modal" @click.stop>
            <div class="modal__icon modal__icon--warning">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 id="delete-modal-title" class="modal__title">Hapus Akun?</h3>
            <p id="delete-modal-desc" class="modal__desc">
                Setelah akun dihapus, semua data akan dihapus secara permanen. Masukkan kata sandi Anda untuk mengkonfirmasi.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="form-group">
                    <label for="delete_password" class="form-label sr-only">Kata Sandi</label>
                    <input
                        id="delete_password"
                        name="password"
                        type="password"
                        class="form-input {{ $errors->userDeletion->has('password') ? 'form-input--error' : '' }}"
                        placeholder="Kata Sandi"
                        autocomplete="current-password"
                        aria-describedby="{{ $errors->userDeletion->has('password') ? 'delete-password-error' : '' }}"
                    >
                    @if ($errors->userDeletion->has('password'))
                        <p id="delete-password-error" class="form-error" aria-live="polite">
                            {{ $errors->userDeletion->first('password') }}
                        </p>
                    @endif
                </div>

                <div class="modal__actions">
                    <button type="button" class="btn btn--secondary" @click="showDeleteModal = false">
                        Batal
                    </button>
                    <button type="submit" class="btn btn--danger">
                        Ya, Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
