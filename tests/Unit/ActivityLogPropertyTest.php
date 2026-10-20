<?php

/**
 * Property-Based Test: Activity Log mencatat setiap aksi login dan logout
 *
 * **Validates: Requirements 1.10**
 *
 * Requirement 1.10: THE Sistem SHALL menyimpan log aktivitas login dan logout
 * setiap pengguna beserta timestamp dan alamat IP.
 */

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\AuthService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Helper: build an IPv4 generator using four octet generators combined into
     * a tuple, then mapped to a dotted-decimal string.
     */
    private function ipv4Generator(): \Eris\Generator
    {
        return Generators::map(
            fn (array $octets) => implode('.', $octets),
            Generators::tuple(
                Generators::choose(0, 255),
                Generators::choose(0, 255),
                Generators::choose(0, 255),
                Generators::choose(0, 255),
            )
        );
    }

    /**
     * Helper: generator that picks one of the three valid roles.
     */
    private function roleGenerator(): \Eris\Generator
    {
        return Generators::elements(['admin', 'kasir', 'gudang']);
    }

    // -------------------------------------------------------------------------
    // Property 12a — Login activity logged correctly
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 1.10**
     *
     * Untuk setiap user (berbagai peran) dan berbagai IP address,
     * memanggil AuthService::logActivity dengan action='login' harus
     * menghasilkan tepat satu entri di activity_logs dengan data yang benar.
     */
    public function test_property_12a_login_activity_logged_correctly(): void
    {
        $this->limitTo(100);

        $this->forAll(
            $this->roleGenerator(),
            $this->ipv4Generator(),
        )->then(function (string $role, string $ip) {
            /** @var User $user */
            $user = User::factory()->create(['role' => $role]);

            /** @var AuthService $service */
            $service = $this->app->make(AuthService::class);
            $service->logActivity($user->id, 'login', $ip);

            // Tepat satu entri harus ada untuk user + action ini
            $this->assertSame(
                1,
                ActivityLog::where('user_id', $user->id)->where('action', 'login')->count(),
                "Harus ada tepat satu entri login untuk user {$user->id}."
            );

            $this->assertDatabaseHas('activity_logs', [
                'user_id'    => $user->id,
                'action'     => 'login',
                'ip_address' => $ip,
            ]);

            // created_at harus diisi (tidak null)
            $log = ActivityLog::where('user_id', $user->id)
                ->where('action', 'login')
                ->first();

            $this->assertNotNull($log, 'Entri activity_log tidak ditemukan.');
            $this->assertNotNull($log->created_at, 'created_at harus diisi.');
        });
    }

    // -------------------------------------------------------------------------
    // Property 12b — Logout activity logged correctly
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 1.10**
     *
     * Untuk setiap user dan IP, memanggil AuthService::logActivity dengan
     * action='logout' harus menghasilkan tepat satu entri dengan data tepat.
     */
    public function test_property_12b_logout_activity_logged_correctly(): void
    {
        $this->limitTo(100);

        $this->forAll(
            $this->roleGenerator(),
            $this->ipv4Generator(),
        )->then(function (string $role, string $ip) {
            /** @var User $user */
            $user = User::factory()->create(['role' => $role]);

            /** @var AuthService $service */
            $service = $this->app->make(AuthService::class);
            $service->logActivity($user->id, 'logout', $ip);

            $this->assertSame(
                1,
                ActivityLog::where('user_id', $user->id)->where('action', 'logout')->count(),
                "Harus ada tepat satu entri logout untuk user {$user->id}."
            );

            $this->assertDatabaseHas('activity_logs', [
                'user_id'    => $user->id,
                'action'     => 'logout',
                'ip_address' => $ip,
            ]);

            $log = ActivityLog::where('user_id', $user->id)
                ->where('action', 'logout')
                ->first();

            $this->assertNotNull($log, 'Entri activity_log tidak ditemukan.');
            $this->assertNotNull($log->created_at, 'created_at harus diisi.');
        });
    }

    // -------------------------------------------------------------------------
    // Property 12c — Multiple sequential log entries are independent
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 1.10**
     *
     * Dua panggilan logActivity berurutan (login lalu logout) untuk user yang
     * sama harus menghasilkan tepat 2 entri yang saling independen:
     * entri pertama = login dengan ip1, entri kedua = logout dengan ip2.
     */
    public function test_property_12c_multiple_sequential_log_entries_are_independent(): void
    {
        $this->limitTo(100);

        $this->forAll(
            $this->roleGenerator(),
            $this->ipv4Generator(),
            $this->ipv4Generator(),
        )->then(function (string $role, string $ip1, string $ip2) {
            /** @var User $user */
            $user = User::factory()->create(['role' => $role]);

            /** @var AuthService $service */
            $service = $this->app->make(AuthService::class);
            $service->logActivity($user->id, 'login', $ip1);
            $service->logActivity($user->id, 'logout', $ip2);

            // Tepat 2 entri untuk user ini (bukan global table count)
            $this->assertSame(
                2,
                ActivityLog::where('user_id', $user->id)->count(),
                "Harus ada tepat 2 entri activity_log untuk user {$user->id}."
            );

            // Entri login dengan ip1 harus ada
            $this->assertDatabaseHas('activity_logs', [
                'user_id'    => $user->id,
                'action'     => 'login',
                'ip_address' => $ip1,
            ]);

            // Entri logout dengan ip2 harus ada
            $this->assertDatabaseHas('activity_logs', [
                'user_id'    => $user->id,
                'action'     => 'logout',
                'ip_address' => $ip2,
            ]);

            // Urutan: login dulu, baru logout
            $logs = ActivityLog::where('user_id', $user->id)
                ->orderBy('id')
                ->get();

            $this->assertSame('login', $logs->get(0)->action, 'Entri pertama harus login.');
            $this->assertSame('logout', $logs->get(1)->action, 'Entri kedua harus logout.');
        });
    }

    // -------------------------------------------------------------------------
    // Property 12d — Via HTTP: login request creates activity log entry
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 1.10**
     *
     * POST /login yang berhasil dengan berbagai user (ketiga peran) dan IP
     * harus membuat satu entri activity_log dengan action='login'.
     */
    public function test_property_12d_http_login_creates_activity_log(): void
    {
        $this->limitTo(50);

        $this->forAll(
            $this->roleGenerator(),
        )->then(function (string $role) {
            /** @var User $user */
            $user = User::factory()->create(['role' => $role]);

            $response = $this->post('/login', [
                'email'    => $user->email,
                'password' => 'password',
            ]);

            // Login harus berhasil (redirect ke dashboard sesuai peran)
            $this->assertAuthenticated();

            // Harus ada satu entri login di activity_logs
            $this->assertDatabaseHas('activity_logs', [
                'user_id' => $user->id,
                'action'  => 'login',
            ]);

            $log = ActivityLog::where('user_id', $user->id)
                ->where('action', 'login')
                ->first();

            $this->assertNotNull($log, 'Entri login di activity_logs tidak ditemukan setelah HTTP login.');
            $this->assertNotNull($log->created_at, 'created_at harus diisi setelah HTTP login.');
            $this->assertNotNull($log->ip_address, 'ip_address harus diisi setelah HTTP login.');

            // Logout agar iterasi berikutnya bisa login lagi
            $this->post('/logout');
        });
    }

    // -------------------------------------------------------------------------
    // Property 12e — Via HTTP: logout creates activity log entry
    // -------------------------------------------------------------------------

    /**
     * **Validates: Requirements 1.10**
     *
     * POST /logout yang dieksekusi oleh user yang sedang login (berbagai peran)
     * harus membuat entri activity_log dengan action='logout'.
     */
    public function test_property_12e_http_logout_creates_activity_log(): void
    {
        $this->limitTo(50);

        $this->forAll(
            $this->roleGenerator(),
        )->then(function (string $role) {
            /** @var User $user */
            $user = User::factory()->create(['role' => $role]);

            // Login terlebih dahulu agar ada sesi
            $this->actingAs($user);

            $this->post('/logout');

            // Harus ada entri logout
            $this->assertDatabaseHas('activity_logs', [
                'user_id' => $user->id,
                'action'  => 'logout',
            ]);

            $log = ActivityLog::where('user_id', $user->id)
                ->where('action', 'logout')
                ->first();

            $this->assertNotNull($log, 'Entri logout di activity_logs tidak ditemukan setelah HTTP logout.');
            $this->assertNotNull($log->created_at, 'created_at harus diisi setelah HTTP logout.');
        });
    }
}
