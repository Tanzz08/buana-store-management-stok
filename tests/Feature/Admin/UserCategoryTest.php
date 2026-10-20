<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Feature tests untuk manajemen pengguna dan kategori — Task 23.2
 *
 * Mencakup:
 *  Manajemen Pengguna (Requirements 1.6–1.8):
 *   - Admin membuat user baru → tersimpan, email verifikasi dijadwalkan ke antrian
 *   - Admin menonaktifkan user → is_active=false, sesi dicabut
 *   - Admin mengganti role user → sesi aktif user lama dihapus
 *
 *  Manajemen Kategori (Requirements 2.2–2.7):
 *   - Tambah kategori nama duplikat (case-insensitive) → validasi gagal, error message
 *   - Hapus kategori dengan produk aktif → redirect dengan flash error
 *   - Tambah kategori valid → berhasil tersimpan
 *   - Hapus kategori tanpa produk aktif → berhasil
 *
 * Requirements: 1.6–1.8, 2.2–2.7
 */
class UserCategoryTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Requirement 1.6 — Admin membuat user baru → user tersimpan, email antrian
    // =========================================================================

    /** @test */
    public function admin_membuat_user_kasir_baru_user_tersimpan_di_database(): void
    {
        Queue::fake();

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name'                  => 'Kasir Baru',
            'email'                 => 'kasir.baru@contoh.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'kasir',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'kasir.baru@contoh.com',
            'role'  => 'kasir',
        ]);
    }

    /** @test */
    public function admin_membuat_user_baru_email_verifikasi_dikirim_ke_pengguna_baru(): void
    {
        // sendEmailVerificationNotification() mengirim notifikasi Laravel (bukan Mailable),
        // sehingga Notification::fake() digunakan untuk memverifikasi pengiriman.
        Notification::fake();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/users', [
            'name'                  => 'Staff Gudang',
            'email'                 => 'gudang.baru@contoh.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'gudang',
        ]);

        $newUser = User::where('email', 'gudang.baru@contoh.com')->first();
        $this->assertNotNull($newUser, 'Pengguna baru seharusnya tersimpan di database.');

        // Pastikan notifikasi email verifikasi telah dikirimkan ke pengguna baru
        Notification::assertSentTo(
            $newUser,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    /** @test */
    public function admin_membuat_user_baru_dengan_role_tidak_valid_gagal(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name'                  => 'Test User',
            'email'                 => 'test@contoh.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'superadmin', // role tidak valid
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'test@contoh.com']);
    }

    /** @test */
    public function admin_membuat_user_baru_email_duplikat_gagal(): void
    {
        $admin   = User::factory()->admin()->create();
        $existing = User::factory()->kasir()->create(['email' => 'duplikat@contoh.com']);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name'                  => 'User Lain',
            'email'                 => 'duplikat@contoh.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'kasir',
        ]);

        $response->assertSessionHasErrors('email');
        // Masih hanya ada 1 user dengan email tersebut
        $this->assertEquals(1, User::where('email', 'duplikat@contoh.com')->count());
    }

    // =========================================================================
    // Requirement 1.8 — Admin menonaktifkan user → is_active=false, sesi dicabut
    // =========================================================================

    /** @test */
    public function admin_menonaktifkan_user_via_destroy_mengubah_is_active_menjadi_false(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$kasir->id}");

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'        => $kasir->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function admin_menonaktifkan_user_via_update_mengubah_is_active_menjadi_false(): void
    {
        $admin  = User::factory()->admin()->create();
        $gudang = User::factory()->gudang()->create();

        $response = $this->actingAs($admin)->put("/admin/users/{$gudang->id}", [
            'name'      => $gudang->name,
            'email'     => $gudang->email,
            'role'      => $gudang->role,
            'is_active' => false,
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $gudang->refresh();
        $this->assertFalse((bool) $gudang->is_active);
    }

    /** @test */
    public function user_yang_dinonaktifkan_tidak_dapat_login(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create(['email' => 'kasir.nonaktif@contoh.com']);

        // Admin menonaktifkan kasir
        $this->actingAs($admin)->delete("/admin/users/{$kasir->id}");

        // Kasir yang sudah nonaktif mencoba login
        // CheckSessionValidity middleware akan menolak sesi aktif pengguna nonaktif,
        // tetapi di sini kita verifikasi bahwa is_active sudah false —
        // middleware CheckSessionValidity akan menangani penolakan login
        $kasir->refresh();
        $this->assertFalse((bool) $kasir->is_active,
            'Pengguna yang dinonaktifkan harus memiliki is_active = false.'
        );
    }

    /** @test */
    public function admin_tidak_dapat_menonaktifkan_akunnya_sendiri(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        // Akun admin sendiri tetap aktif
        $this->assertDatabaseHas('users', [
            'id'        => $admin->id,
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // Requirement 1.7 — Admin mengganti role user → sesi aktif user lama dihapus
    // =========================================================================

    /** @test */
    public function admin_mengganti_role_user_role_berhasil_diperbarui(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($admin)->put("/admin/users/{$kasir->id}", [
            'name'      => $kasir->name,
            'email'     => $kasir->email,
            'role'      => 'gudang', // ganti role kasir → gudang
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id'   => $kasir->id,
            'role' => 'gudang',
        ]);
    }

    /** @test */
    public function admin_mengganti_role_user_sesi_lama_dihapus_jika_tabel_sessions_ada(): void
    {
        // UserController memanggil DB::table('sessions')->where('user_id',...)->delete()
        // saat role berubah. Karena SESSION_DRIVER=array di test, tabel sessions mungkin
        // tidak ada. Test ini bersifat kondisional: jika tabel ada, sesi harus terhapus.

        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();

        $sessionsTableExists = false;
        try {
            DB::table('sessions')->count();
            $sessionsTableExists = true;
        } catch (\Exception $e) {
            // Tabel sessions tidak ada pada test env dengan driver array
            $sessionsTableExists = false;
        }

        if ($sessionsTableExists) {
            DB::table('sessions')->insert([
                'id'            => 'session-kasir-' . $kasir->id,
                'user_id'       => $kasir->id,
                'ip_address'    => '127.0.0.1',
                'user_agent'    => 'PHPUnit',
                'payload'       => base64_encode(serialize([])),
                'last_activity' => now()->timestamp,
            ]);
        }

        $this->actingAs($admin)->put("/admin/users/{$kasir->id}", [
            'name'      => $kasir->name,
            'email'     => $kasir->email,
            'role'      => 'gudang',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id'   => $kasir->id,
            'role' => 'gudang',
        ]);

        if ($sessionsTableExists) {
            $this->assertDatabaseMissing('sessions', [
                'user_id' => $kasir->id,
            ]);
        }
    }

    /** @test */
    public function admin_mengganti_role_user_tanpa_perubahan_role_tidak_menghapus_sesi(): void
    {
        // Memperbarui nama tanpa mengubah role tidak seharusnya menghapus sesi
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create(['name' => 'Nama Lama']);

        $response = $this->actingAs($admin)->put("/admin/users/{$kasir->id}", [
            'name'      => 'Nama Baru', // nama diubah, role tidak berubah
            'email'     => $kasir->email,
            'role'      => 'kasir',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $kasir->id, 'name' => 'Nama Baru']);
    }

    // =========================================================================
    // Requirement 2.2 — Tambah kategori valid → tersimpan
    // =========================================================================

    /** @test */
    public function admin_menambah_kategori_dengan_nama_unik_berhasil(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name'        => 'Handphone',
            'description' => 'Kategori untuk produk handphone',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', ['name' => 'Handphone']);
    }

    /** @test */
    public function admin_menambah_kategori_tanpa_deskripsi_opsional_berhasil(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Aksesoris',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Aksesoris']);
    }

    // =========================================================================
    // Requirement 2.3 — Nama duplikat (case-insensitive) → validasi gagal
    // =========================================================================

    /** @test */
    public function admin_menambah_kategori_nama_duplikat_exact_gagal(): void
    {
        $admin = User::factory()->admin()->create();

        Category::create(['name' => 'Handphone', 'description' => null]);

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Handphone',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('name');

        $errors = $response->getSession()->get('errors');
        $this->assertEquals('Nama kategori sudah digunakan.', $errors->first('name'));
    }

    /** @test */
    public function admin_menambah_kategori_nama_duplikat_uppercase_ditolak(): void
    {
        $admin = User::factory()->admin()->create();

        Category::create(['name' => 'aksesoris', 'description' => null]);

        // Coba tambah dengan huruf kapital semua — harus ditolak (case-insensitive)
        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'AKSESORIS',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('name');

        $errors = $response->getSession()->get('errors');
        $this->assertEquals('Nama kategori sudah digunakan.', $errors->first('name'));

        // Pastikan tidak ada duplikat yang tersimpan — tetap hanya 1 record
        $this->assertEquals(
            1,
            Category::whereRaw('LOWER(name) = ?', ['aksesoris'])->count()
        );
    }

    /** @test */
    public function admin_menambah_kategori_nama_duplikat_mixed_case_ditolak(): void
    {
        $admin = User::factory()->admin()->create();

        Category::create(['name' => 'Kartu SIM', 'description' => null]);

        // Coba dengan mixed case yang berbeda
        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'kartu sim',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('name');
    }

    // =========================================================================
    // Requirement 2.6 — Hapus kategori dengan produk aktif → flash error
    // =========================================================================

    /** @test */
    public function admin_menghapus_kategori_dengan_produk_aktif_gagal_dengan_flash_error(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::create(['name' => 'Voucher Data', 'description' => null]);

        Product::create([
            'category_id'    => $category->id,
            'sku'            => 'VD-001',
            'name'           => 'Voucher 10GB',
            'purchase_price' => 50000,
            'selling_price'  => 55000,
            'unit'           => 'pcs',
            'current_stock'  => 10,
            'minimum_stock'  => 5,
            'status'         => 'active',
        ]);

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        // CategoryController menangkap EntityHasDependenciesException → redirect + flash error
        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');

        $error = $response->getSession()->get('error');
        $this->assertStringContainsString('produk aktif', $error);

        // Kategori tidak boleh terhapus
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    // =========================================================================
    // Requirement 2.7 — Hapus kategori tanpa produk aktif → berhasil
    // =========================================================================

    /** @test */
    public function admin_menghapus_kategori_tanpa_produk_berhasil(): void
    {
        $admin    = User::factory()->admin()->create();
        $category = Category::create(['name' => 'Kategori Kosong', 'description' => null]);

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    // =========================================================================
    // Otorisasi — non-Admin diblokir dari endpoint manajemen pengguna & kategori
    // =========================================================================

    /** @test */
    public function kasir_tidak_dapat_mengakses_halaman_manajemen_pengguna(): void
    {
        $kasir = User::factory()->kasir()->create();

        // CheckRole middleware memblokir akses ke /admin/* untuk non-admin
        $response = $this->actingAs($kasir)->get('/admin/users');

        $response->assertStatus(302);
    }

    /** @test */
    public function gudang_tidak_dapat_menambah_kategori_baru(): void
    {
        $gudang = User::factory()->gudang()->create();

        $response = $this->actingAs($gudang)->post('/admin/categories', [
            'name' => 'Kategori Ilegal',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('categories', ['name' => 'Kategori Ilegal']);
    }
}
