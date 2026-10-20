<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests untuk modul Autentikasi — Task 23.1
 *
 * Mencakup:
 * - UI halaman login menggunakan vanilla CSS (bukan Tailwind)
 * - Login sukses → redirect ke dashboard sesuai role
 * - Login gagal berulang → rate limiting dengan pesan terkunci
 * - Logout → sesi dihapus, redirect /login
 * - Password reset anti-enumeration (email tidak terdaftar tetap tampil konfirmasi)
 *
 * Requirements: 1.1–1.5, 10.1
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Requirement 1.1 — Halaman login dapat diakses tanpa autentikasi
    // =========================================================================

    #[Test]
    public function halaman_login_dapat_diakses_tanpa_autentikasi(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    #[Test]
    public function halaman_login_menggunakan_class_vanilla_css_guest_card(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('guest-card', false);
    }

    #[Test]
    public function halaman_login_menggunakan_class_form_input_vanilla_css(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('form-input', false);
    }

    #[Test]
    public function halaman_login_menggunakan_class_btn_primary_vanilla_css(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('btn--primary', false);
    }

    #[Test]
    public function halaman_login_tidak_mengandung_class_tailwind(): void
    {
        $response = $this->get('/login');

        $content = $response->getContent();

        // Pastikan tidak ada utility class Tailwind khas di HTML
        $this->assertStringNotContainsString('class="bg-gray', $content);
        $this->assertStringNotContainsString('class="text-gray', $content);
        $this->assertStringNotContainsString('class="flex ', $content);
        $this->assertStringNotContainsString('class="px-', $content);
        $this->assertStringNotContainsString('class="py-', $content);
        $this->assertStringNotContainsString('class="rounded-', $content);
        $this->assertStringNotContainsString('class="border-', $content);
    }

    // =========================================================================
    // Requirement 1.2 — Login sukses → redirect ke dashboard sesuai role
    // =========================================================================

    #[Test]
    public function login_sukses_admin_redirect_ke_admin_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/admin/dashboard');
    }

    #[Test]
    public function login_sukses_kasir_redirect_ke_kasir_dashboard(): void
    {
        $user = User::factory()->kasir()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/kasir/dashboard');
    }

    #[Test]
    public function login_sukses_gudang_redirect_ke_gudang_dashboard(): void
    {
        $user = User::factory()->gudang()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/gudang/dashboard');
    }

    // =========================================================================
    // Requirement 1.3 — Login gagal → pesan error tanpa bocorkan info sensitif
    // =========================================================================

    #[Test]
    public function login_gagal_kata_sandi_salah_tampilkan_pesan_error(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'kata-sandi-salah',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        $errors = $response->getSession()->get('errors');
        $message = $errors->first('email');
        $this->assertEquals('Email atau kata sandi tidak valid.', $message);
    }

    #[Test]
    public function login_gagal_tidak_mengungkap_apakah_email_terdaftar(): void
    {
        // Email tidak terdaftar harus memberikan pesan error yang sama
        $response = $this->post('/login', [
            'email'    => 'tidak-ada@contoh.com',
            'password' => 'sembarang-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        $errors = $response->getSession()->get('errors');
        $message = $errors->first('email');
        // Pesan sama dengan login gagal biasa — tidak ada petunjuk "email tidak ditemukan"
        $this->assertEquals('Email atau kata sandi tidak valid.', $message);
    }

    // =========================================================================
    // Requirement 1.5 — Rate limiting: 5 percobaan → pesan terkunci
    //
    // Catatan implementasi: LoginRequest menggunakan RateLimiter internal yang
    // melempar ValidationException (bukan middleware throttle di route), sehingga
    // respons yang dikembalikan adalah 302 redirect dengan pesan terkunci di
    // session errors — bukan HTTP 429 langsung.
    // =========================================================================

    #[Test]
    public function login_gagal_5_kali_berturut_diblokir_rate_limiter(): void
    {
        // Pastikan rate limiter bersih sebelum test
        RateLimiter::clear('tidak-ada@contoh.com|127.0.0.1');

        $payload = [
            'email'    => 'tidak-ada@contoh.com',
            'password' => 'salah',
        ];

        // 5 percobaan pertama — masing-masing gagal dengan error validasi biasa
        for ($i = 0; $i < 5; $i++) {
            $resp = $this->post('/login', $payload);
            $resp->assertStatus(302);
            $resp->assertSessionHasErrors('email');
        }

        // Percobaan ke-6 harus diblokir rate limiter dengan pesan terkunci
        $response = $this->post('/login', $payload);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = $response->getSession()->get('errors');
        $this->assertStringContainsString('terkunci', $errors->first('email'));
    }

    #[Test]
    public function setelah_rate_limit_pesan_terkunci_15_menit_muncul(): void
    {
        RateLimiter::clear('tidak-ada@contoh.com|127.0.0.1');

        $payload = [
            'email'    => 'tidak-ada@contoh.com',
            'password' => 'salah',
        ];

        // Habiskan 5 percobaan
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', $payload);
        }

        // Percobaan ke-6: pesan kunci akun harus tepat sesuai requirement 1.5
        $response = $this->post('/login', $payload);

        $response->assertSessionHasErrors('email');
        $errors = $response->getSession()->get('errors');
        $this->assertStringContainsString(
            'Akun terkunci selama 15 menit karena terlalu banyak percobaan login.',
            $errors->first('email')
        );
    }

    // =========================================================================
    // Requirement 1.9 — Logout → sesi dihapus, redirect /login
    // =========================================================================

    #[Test]
    public function logout_menghapus_sesi_dan_redirect_ke_login(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function logout_mencabut_autentikasi_pengguna(): void
    {
        $user = User::factory()->kasir()->create();

        $this->actingAs($user)->post('/logout');

        // Setelah logout, pengguna tidak lagi terautentikasi
        $this->assertGuest();
    }

    #[Test]
    public function setelah_logout_akses_dashboard_diarahkan_ke_login(): void
    {
        $user = User::factory()->admin()->create();

        // Login dulu
        $this->actingAs($user);

        // Logout
        $this->post('/logout');

        // Akses dashboard setelah logout harus redirect ke login
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    // =========================================================================
    // Requirement 1.4 — Password reset anti-enumeration
    // =========================================================================

    #[Test]
    public function halaman_lupa_password_dapat_diakses(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    #[Test]
    public function forgot_password_email_terdaftar_tampilkan_konfirmasi(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');

        $status = $response->getSession()->get('status');
        $this->assertStringContainsString(
            'Jika email tersebut terdaftar',
            $status
        );
    }

    #[Test]
    public function forgot_password_email_tidak_terdaftar_tetap_tampilkan_konfirmasi(): void
    {
        // Anti-enumeration: email tidak terdaftar HARUS menampilkan pesan yang SAMA
        $response = $this->post('/forgot-password', [
            'email' => 'tidak-terdaftar@contoh.com',
        ]);

        $response->assertSessionHas('status');

        $status = $response->getSession()->get('status');
        $this->assertStringContainsString(
            'Jika email tersebut terdaftar',
            $status
        );
    }

    #[Test]
    public function forgot_password_email_terdaftar_dan_tidak_terdaftar_pesan_sama(): void
    {
        $user = User::factory()->create();

        // Kirim untuk email terdaftar
        $responseTerdaftar = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);
        $statusTerdaftar = $responseTerdaftar->getSession()->get('status');

        // Kirim untuk email tidak terdaftar
        $responseTidakTerdaftar = $this->post('/forgot-password', [
            'email' => 'tidak-ada@contoh.com',
        ]);
        $statusTidakTerdaftar = $responseTidakTerdaftar->getSession()->get('status');

        // Kedua pesan harus identik — mencegah enumerasi email
        $this->assertEquals($statusTerdaftar, $statusTidakTerdaftar);
    }

    #[Test]
    public function forgot_password_validasi_format_email_tidak_valid(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => 'bukan-email-valid',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function forgot_password_validasi_email_wajib_diisi(): void
    {
        $response = $this->post('/forgot-password', [
            'email' => '',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
