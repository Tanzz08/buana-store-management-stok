<x-app-layout>
    <x-slot name="header">
        <h1 class="navbar__page-title">Profil Saya</h1>
    </x-slot>

    <div class="page-body">
        {{-- Update Profile Information --}}
        <div class="profile-section">
            <h2 class="profile-section__title">Informasi Profil</h2>
            <p class="profile-section__desc">Perbarui informasi profil dan alamat email akun Anda.</p>
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Update Password --}}
        <div class="profile-section">
            <h2 class="profile-section__title">Ubah Kata Sandi</h2>
            <p class="profile-section__desc">Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.</p>
            @include('profile.partials.update-password-form')
        </div>

        {{-- Delete Account --}}
        <div class="profile-section">
            <h2 class="profile-section__title">Hapus Akun</h2>
            <p class="profile-section__desc">Setelah akun dihapus, semua data dan sumber daya akan dihapus secara permanen.</p>
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
