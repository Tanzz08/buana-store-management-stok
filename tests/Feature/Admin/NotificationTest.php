<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\LowStockNotification;
use App\Models\NotificationRead;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk modul Notifikasi Stok Rendah (Requirements 8.1–8.6).
 *
 * Memverifikasi:
 * - Stok turun ke minimum_stock → tepat 1 notifikasi aktif terbuat (tidak duplikat)
 * - Stok masuk memulihkan stok di atas minimum → notifikasi aktif ter-resolve otomatis
 * - Tandai satu notifikasi dibaca → badge count berkurang 1
 * - Tandai semua notifikasi dibaca → badge count = 0
 * - Polling GET /api/notifications/count → mengembalikan JSON {"count": N}
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;
    private Product $product;
    private Supplier $supplier;
    private NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $this->category = Category::create([
            'name'        => 'Aksesoris',
            'description' => 'Aksesoris handphone',
        ]);

        $this->product = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'AKS-001',
            'name'           => 'Casing iPhone 15',
            'purchase_price' => 50000,
            'selling_price'  => 85000,
            'unit'           => 'pcs',
            'current_stock'  => 10,
            'minimum_stock'  => 5,
            'status'         => 'active',
        ]);

        $this->supplier = Supplier::create([
            'name'  => 'PT Aksesoris Nusantara',
            'phone' => '02134567890',
        ]);

        $this->notificationService = app(NotificationService::class);
    }

    // -------------------------------------------------------------------------
    // Requirements 8.1 — Notifikasi terbuat saat stok <= minimum, tidak duplikat
    // -------------------------------------------------------------------------

    /**
     * @test
     * Stok turun ke tepat minimum_stock harus membuat tepat 1 notifikasi aktif.
     * Requirements: 8.1
     */
    public function stok_turun_ke_minimum_stock_membuat_tepat_satu_notifikasi_aktif(): void
    {
        // Stok awal 10, minimum 5 — turun ke minimum dengan penyesuaian stok
        $stockService = app(StockService::class);
        $stockService->adjustStock(
            productId:   $this->product->id,
            actualStock: 5,           // tepat sama dengan minimum_stock
            reason:      'Stok opname',
            userId:      $this->admin->id,
        );

        $activeCount = LowStockNotification::where('product_id', $this->product->id)
            ->active()
            ->count();

        $this->assertEquals(
            1,
            $activeCount,
            'Harus terbuat tepat 1 notifikasi aktif saat stok turun ke minimum_stock.'
        );
    }

    /**
     * @test
     * Notifikasi kedua tidak terbuat jika notifikasi aktif untuk produk sudah ada (tidak duplikat).
     * Requirements: 8.1
     */
    public function tidak_ada_duplikasi_notifikasi_aktif_untuk_produk_yang_sama(): void
    {
        // Panggil createIfNotExists dua kali untuk produk yang sama
        $this->notificationService->createIfNotExists($this->product->id, 5);
        $this->notificationService->createIfNotExists($this->product->id, 4);

        $activeCount = LowStockNotification::where('product_id', $this->product->id)
            ->active()
            ->count();

        $this->assertEquals(
            1,
            $activeCount,
            'createIfNotExists tidak boleh membuat duplikat notifikasi aktif untuk produk yang sama.'
        );
    }

    /**
     * @test
     * Stok di bawah minimum melalui penjualan memicu pembuatan notifikasi.
     * Requirements: 8.1
     */
    public function penjualan_yang_menurunkan_stok_ke_minimum_memicu_notifikasi(): void
    {
        // Stok 6, minimum 5 — jual 1 unit agar stok menjadi 5 (= minimum)
        $this->product->update(['current_stock' => 6]);

        $kasir = User::factory()->kasir()->create();

        $this->actingAs($kasir)->post('/kasir/sales', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity'   => 1,
                ],
            ],
        ]);

        $this->assertDatabaseHas('low_stock_notifications', [
            'product_id' => $this->product->id,
            'status'     => 'unread',
        ]);
    }

    // -------------------------------------------------------------------------
    // Requirements 8.6 — Stok pulih di atas minimum → notifikasi aktif ter-resolve
    // -------------------------------------------------------------------------

    /**
     * @test
     * Stok masuk yang memulihkan stok di atas minimum_stock harus meng-resolve notifikasi aktif.
     * Requirements: 8.6
     */
    public function stok_masuk_yang_memulihkan_stok_di_atas_minimum_resolve_notifikasi(): void
    {
        // Buat notifikasi aktif terlebih dahulu (stok = 3, minimum = 5)
        $this->product->update(['current_stock' => 3]);
        $this->notificationService->createIfNotExists($this->product->id, 3);

        $this->assertDatabaseHas('low_stock_notifications', [
            'product_id' => $this->product->id,
            'status'     => 'unread',
        ]);

        // Catat stok masuk yang membuat stok > minimum (3 + 10 = 13 > 5)
        $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 10,
            'unit_price'  => 45000,
            'received_at' => now()->toDateString(),
        ]);

        // Notifikasi aktif harus ter-resolve
        $activeCount = LowStockNotification::where('product_id', $this->product->id)
            ->active()
            ->count();

        $this->assertEquals(
            0,
            $activeCount,
            'Semua notifikasi aktif harus ter-resolve setelah stok kembali di atas minimum_stock.'
        );

        // Status notifikasi harus berubah menjadi 'resolved'
        $this->assertDatabaseHas('low_stock_notifications', [
            'product_id' => $this->product->id,
            'status'     => 'resolved',
        ]);
    }

    /**
     * @test
     * Stok masuk yang hanya menyamakan stok dengan minimum (tidak di atas) tidak meng-resolve notifikasi.
     * Requirements: 8.6
     */
    public function stok_masuk_yang_hanya_mencapai_minimum_tidak_resolve_notifikasi(): void
    {
        // Stok = 2, minimum = 5 — masukkan 3 unit agar stok = 5 (= minimum, bukan > minimum)
        $this->product->update(['current_stock' => 2]);
        $this->notificationService->createIfNotExists($this->product->id, 2);

        $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 3,   // 2 + 3 = 5, tepat sama dengan minimum_stock
            'unit_price'  => 45000,
            'received_at' => now()->toDateString(),
        ]);

        // Stok = minimum, notifikasi belum seharusnya di-resolve
        $activeCount = LowStockNotification::where('product_id', $this->product->id)
            ->active()
            ->count();

        $this->assertEquals(
            1,
            $activeCount,
            'Notifikasi tidak boleh ter-resolve jika stok hanya sama dengan minimum_stock (harus > minimum).'
        );
    }

    // -------------------------------------------------------------------------
    // Requirements 8.4 — Tandai satu notifikasi → badge count berkurang 1
    // -------------------------------------------------------------------------

    /**
     * @test
     * Menandai satu notifikasi sebagai sudah dibaca harus mengurangi badge count sebesar 1.
     * Requirements: 8.4
     */
    public function tandai_satu_notifikasi_dibaca_mengurangi_badge_count_satu(): void
    {
        // Buat 3 notifikasi unread untuk 3 produk berbeda
        [$notif1, $notif2, $notif3] = $this->buatTigaNotifikasiUnread();

        $countSebelum = $this->notificationService->getUnreadCountForUser($this->admin->id);
        $this->assertEquals(3, $countSebelum, 'Badge count awal harus 3.');

        // Tandai satu notifikasi sebagai dibaca
        $response = $this->actingAs($this->admin)
            ->post("/admin/notifications/{$notif1->id}/read");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $countSesudah = $this->notificationService->getUnreadCountForUser($this->admin->id);
        $this->assertEquals(
            2,
            $countSesudah,
            'Badge count harus berkurang 1 setelah menandai satu notifikasi sebagai dibaca.'
        );
    }

    /**
     * @test
     * Record NotificationRead terbuat di database saat menandai satu notifikasi dibaca.
     * Requirements: 8.4
     */
    public function menandai_satu_notifikasi_dibaca_membuat_record_notification_read(): void
    {
        $notif = LowStockNotification::create([
            'product_id'        => $this->product->id,
            'stock_at_creation' => 4,
            'status'            => 'unread',
            'created_at'        => now(),
        ]);

        $this->actingAs($this->admin)
            ->post("/admin/notifications/{$notif->id}/read");

        $this->assertDatabaseHas('notification_reads', [
            'notification_id' => $notif->id,
            'user_id'         => $this->admin->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Requirements 8.5 — Tandai semua → badge count = 0
    // -------------------------------------------------------------------------

    /**
     * @test
     * Menandai semua notifikasi sebagai dibaca harus mereset badge count menjadi 0.
     * Requirements: 8.5
     */
    public function tandai_semua_notifikasi_dibaca_mereset_badge_count_menjadi_nol(): void
    {
        $this->buatTigaNotifikasiUnread();

        $countSebelum = $this->notificationService->getUnreadCountForUser($this->admin->id);
        $this->assertEquals(3, $countSebelum, 'Badge count awal harus 3.');

        $response = $this->actingAs($this->admin)
            ->post('/admin/notifications/read-all');

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $countSesudah = $this->notificationService->getUnreadCountForUser($this->admin->id);
        $this->assertEquals(
            0,
            $countSesudah,
            'Badge count harus 0 setelah menandai semua notifikasi sebagai dibaca.'
        );
    }

    /**
     * @test
     * Badge count pengguna lain tidak terpengaruh saat satu pengguna menandai semua dibaca.
     * Requirements: 8.5
     */
    public function badge_count_pengguna_lain_tidak_terpengaruh_saat_mark_all_read(): void
    {
        $gudang = User::factory()->gudang()->create();
        $this->buatTigaNotifikasiUnread();

        // Admin tandai semua
        $this->actingAs($this->admin)->post('/admin/notifications/read-all');

        // Badge count gudang harus tetap 3 (belum ditandai oleh gudang)
        $countGudang = $this->notificationService->getUnreadCountForUser($gudang->id);
        $this->assertEquals(
            3,
            $countGudang,
            'Badge count pengguna lain tidak boleh berubah saat pengguna lain menandai semua dibaca.'
        );
    }

    // -------------------------------------------------------------------------
    // Requirements 8.2 — Polling GET /api/notifications/count → JSON {"count": N}
    // -------------------------------------------------------------------------

    /**
     * @test
     * Endpoint GET /api/notifications/count mengembalikan JSON {"count": N} sesuai jumlah unread.
     * Requirements: 8.2
     */
    public function api_notifications_count_mengembalikan_json_count_sesuai_unread(): void
    {
        $this->buatTigaNotifikasiUnread();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/notifications/count');

        $response->assertStatus(200);
        $response->assertJson(['count' => 3]);
        $response->assertJsonStructure(['count']);
    }

    /**
     * @test
     * Endpoint /api/notifications/count mengembalikan count = 0 jika tidak ada notifikasi unread.
     * Requirements: 8.2
     */
    public function api_notifications_count_mengembalikan_nol_jika_tidak_ada_unread(): void
    {
        // Tidak ada notifikasi sama sekali
        $response = $this->actingAs($this->admin)
            ->getJson('/api/notifications/count');

        $response->assertStatus(200);
        $response->assertJson(['count' => 0]);
    }

    /**
     * @test
     * Endpoint /api/notifications/count count berubah setelah mark all read.
     * Requirements: 8.2, 8.5
     */
    public function api_notifications_count_berkurang_setelah_mark_all_read(): void
    {
        $this->buatTigaNotifikasiUnread();

        // Sebelum mark all
        $response = $this->actingAs($this->admin)->getJson('/api/notifications/count');
        $response->assertJson(['count' => 3]);

        // Mark all
        $this->actingAs($this->admin)->post('/admin/notifications/read-all');

        // Sesudah mark all
        $response = $this->actingAs($this->admin)->getJson('/api/notifications/count');
        $response->assertJson(['count' => 0]);
    }

    /**
     * @test
     * Endpoint /api/notifications/count memerlukan autentikasi.
     * Requirements: 8.2
     */
    public function api_notifications_count_memerlukan_autentikasi(): void
    {
        $response = $this->getJson('/api/notifications/count');

        // Unauthenticated request harus ditolak (401)
        $response->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // Requirements 8.3 — Halaman notifikasi menampilkan daftar produk stok rendah
    // -------------------------------------------------------------------------

    /**
     * @test
     * Halaman notifikasi admin dapat diakses dan menampilkan notifikasi yang ada.
     * Requirements: 8.3
     */
    public function halaman_notifikasi_admin_dapat_diakses_dan_menampilkan_daftar(): void
    {
        $this->notificationService->createIfNotExists($this->product->id, 3);

        $response = $this->actingAs($this->admin)->get('/admin/notifications');

        $response->assertStatus(200);
        $response->assertSee($this->product->name);
        $response->assertSee($this->product->sku);
    }

    // -------------------------------------------------------------------------
    // Helper privat
    // -------------------------------------------------------------------------

    /**
     * Buat 3 notifikasi unread untuk 3 produk berbeda dan kembalikan array-nya.
     *
     * @return array{0: LowStockNotification, 1: LowStockNotification, 2: LowStockNotification}
     */
    private function buatTigaNotifikasiUnread(): array
    {
        $product2 = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'AKS-002',
            'name'           => 'Tempered Glass iPhone 15',
            'purchase_price' => 15000,
            'selling_price'  => 30000,
            'unit'           => 'pcs',
            'current_stock'  => 2,
            'minimum_stock'  => 5,
            'status'         => 'active',
        ]);

        $product3 = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'AKS-003',
            'name'           => 'Charger USB-C 65W',
            'purchase_price' => 80000,
            'selling_price'  => 130000,
            'unit'           => 'pcs',
            'current_stock'  => 1,
            'minimum_stock'  => 3,
            'status'         => 'active',
        ]);

        $notif1 = LowStockNotification::create([
            'product_id'        => $this->product->id,
            'stock_at_creation' => 4,
            'status'            => 'unread',
            'created_at'        => now()->subMinutes(10),
        ]);

        $notif2 = LowStockNotification::create([
            'product_id'        => $product2->id,
            'stock_at_creation' => 2,
            'status'            => 'unread',
            'created_at'        => now()->subMinutes(5),
        ]);

        $notif3 = LowStockNotification::create([
            'product_id'        => $product3->id,
            'stock_at_creation' => 1,
            'status'            => 'unread',
            'created_at'        => now(),
        ]);

        return [$notif1, $notif2, $notif3];
    }
}
