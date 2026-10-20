<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk modul Penyesuaian Stok (Stok Opname) (Requirements 7.1–7.6).
 *
 * Memverifikasi:
 * - Penyesuaian valid menetapkan current_stock ke nilai aktual, mencatat difference dan riwayat
 * - Validasi menolak stock_after negatif dan field wajib kosong
 * - Filter riwayat penyesuaian berdasarkan produk dan rentang tanggal
 */
class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;
    private Product $product;

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
            'sku'            => 'AK-001',
            'name'           => 'Charger USB-C 65W',
            'purchase_price' => 150000,
            'selling_price'  => 200000,
            'unit'           => 'unit',
            'current_stock'  => 50,
            'minimum_stock'  => 10,
            'status'         => 'active',
        ]);
    }

    // -------------------------------------------------------------------------
    // Penyesuaian stok valid
    // -------------------------------------------------------------------------

    /**
     * @test
     * Penyesuaian stok valid harus menetapkan current_stock ke nilai aktual (stock_after).
     * Requirements: 7.1, 7.2
     */
    public function penyesuaian_stok_valid_menetapkan_current_stock_ke_nilai_aktual(): void
    {
        $actualStock = 35; // kurang dari stok sebelumnya (50)

        $response = $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => $actualStock,
            'reason'      => 'Hasil perhitungan stok opname bulanan.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals(
            $actualStock,
            $this->product->current_stock,
            'current_stock harus sama dengan nilai stock_after yang ditetapkan.'
        );
    }

    /**
     * @test
     * Penyesuaian stok harus mencatat difference yang benar (stock_after − stock_before).
     * Requirements: 7.2, 7.3
     */
    public function penyesuaian_stok_mencatat_difference_yang_benar(): void
    {
        $stockBefore = $this->product->current_stock; // 50
        $actualStock = 35;
        $expectedDiff = $actualStock - $stockBefore; // -15

        $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => $actualStock,
            'reason'      => 'Koreksi stok setelah audit gudang.',
        ]);

        $adjustment = StockAdjustment::latest('id')->first();

        $this->assertNotNull($adjustment, 'Record penyesuaian stok harus tersimpan.');
        $this->assertEquals($stockBefore, $adjustment->stock_before, 'stock_before harus mencatat stok sebelum penyesuaian.');
        $this->assertEquals($actualStock, $adjustment->stock_after, 'stock_after harus sama dengan nilai aktual.');
        $this->assertEquals($expectedDiff, $adjustment->difference, 'difference harus sama dengan stock_after − stock_before.');
    }

    /**
     * @test
     * Penyesuaian stok harus mencatat riwayat dengan user_id, reason, dan adjusted_at.
     * Requirements: 7.4, 7.5
     */
    public function penyesuaian_stok_mencatat_riwayat_lengkap_dengan_user_dan_alasan(): void
    {
        $reason = 'Koreksi stok setelah menerima laporan kehilangan barang.';

        $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => 40,
            'reason'      => $reason,
        ]);

        $adjustment = StockAdjustment::latest('id')->first();

        $this->assertNotNull($adjustment);
        $this->assertEquals($this->admin->id, $adjustment->user_id, 'user_id harus diisi dengan ID pengguna yang melakukan penyesuaian.');
        $this->assertEquals($reason, $adjustment->reason, 'reason harus tersimpan sesuai input.');
        $this->assertNotNull($adjustment->adjusted_at, 'adjusted_at harus diisi dengan timestamp penyesuaian.');

        $this->assertDatabaseHas('stock_adjustments', [
            'product_id'  => $this->product->id,
            'user_id'     => $this->admin->id,
            'stock_before' => 50,
            'stock_after'  => 40,
            'difference'   => -10,
            'reason'       => $reason,
        ]);
    }

    /**
     * @test
     * Penyesuaian stok ke nilai nol harus valid (stok bisa habis).
     * Requirements: 7.1
     */
    public function penyesuaian_stok_ke_nilai_nol_adalah_valid(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => 0,
            'reason'      => 'Stok habis terjual semua secara manual.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals(0, $this->product->current_stock);
    }

    /**
     * @test
     * Penyesuaian stok ke nilai lebih tinggi dari sebelumnya harus menghasilkan difference positif.
     * Requirements: 7.2
     */
    public function penyesuaian_stok_naik_mencatat_difference_positif(): void
    {
        $stockBefore = $this->product->current_stock; // 50
        $actualStock = 75;

        $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => $actualStock,
            'reason'      => 'Ditemukan stok tambahan yang belum tercatat sebelumnya.',
        ]);

        $adjustment = StockAdjustment::latest('id')->first();

        $this->assertEquals($actualStock - $stockBefore, $adjustment->difference);
        $this->assertGreaterThan(0, $adjustment->difference, 'difference harus positif ketika stok naik.');
    }

    // -------------------------------------------------------------------------
    // Validasi input
    // -------------------------------------------------------------------------

    /**
     * @test
     * Penyesuaian stok ke nilai negatif harus ditolak validasi, stok tidak berubah.
     * Requirements: 7.6
     */
    public function penyesuaian_stok_ke_nilai_negatif_ditolak(): void
    {
        $stockBefore = $this->product->current_stock;

        $response = $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => -1,
            'reason'      => 'Percobaan input tidak valid.',
        ]);

        $response->assertSessionHasErrors('stock_after');

        $this->product->refresh();
        $this->assertEquals(
            $stockBefore,
            $this->product->current_stock,
            'current_stock tidak boleh berubah ketika validasi gagal.'
        );

        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    /**
     * @test
     * Penyesuaian stok tanpa field reason harus ditolak validasi.
     * Requirements: 7.5
     */
    public function penyesuaian_stok_tanpa_alasan_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => 30,
            'reason'      => '',
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    /**
     * @test
     * Penyesuaian stok dengan reason lebih dari 255 karakter harus ditolak validasi.
     * Requirements: 7.5
     */
    public function penyesuaian_stok_dengan_alasan_terlalu_panjang_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => 30,
            'reason'      => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('reason');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    /**
     * @test
     * Penyesuaian stok dengan product_id tidak terdaftar harus ditolak validasi.
     * Requirements: 7.6
     */
    public function penyesuaian_stok_dengan_produk_tidak_terdaftar_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => 99999,
            'stock_after' => 30,
            'reason'      => 'Test produk tidak valid.',
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    /**
     * @test
     * Penyesuaian stok dengan stock_after melebihi 999.999 harus ditolak.
     * Requirements: 7.6
     */
    public function penyesuaian_stok_melebihi_batas_maksimum_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => 1000000,
            'reason'      => 'Nilai stok melebihi batas maksimum.',
        ]);

        $response->assertSessionHasErrors('stock_after');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    /**
     * @test
     * Penyesuaian stok pada produk tidak aktif harus ditolak validasi.
     * Requirements: 7.6
     */
    public function penyesuaian_stok_pada_produk_tidak_aktif_ditolak(): void
    {
        $inactiveProduct = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'AK-INACTIVE',
            'name'           => 'Produk Tidak Aktif',
            'purchase_price' => 100000,
            'selling_price'  => 150000,
            'unit'           => 'unit',
            'current_stock'  => 20,
            'minimum_stock'  => 5,
            'status'         => 'inactive',
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $inactiveProduct->id,
            'stock_after' => 15,
            'reason'      => 'Penyesuaian produk tidak aktif.',
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('stock_adjustments', 0);
    }

    // -------------------------------------------------------------------------
    // Filter riwayat penyesuaian stok
    // -------------------------------------------------------------------------

    /**
     * @test
     * Filter riwayat penyesuaian berdasarkan produk menghasilkan hanya record produk tersebut.
     * Requirements: 7.4
     */
    public function filter_riwayat_penyesuaian_berdasarkan_produk_menghasilkan_data_benar(): void
    {
        $product2 = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'AK-002',
            'name'           => 'Kabel Data USB-C',
            'purchase_price' => 50000,
            'selling_price'  => 75000,
            'unit'           => 'unit',
            'current_stock'  => 30,
            'minimum_stock'  => 5,
            'status'         => 'active',
        ]);

        // Penyesuaian untuk produk 1
        $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => 45,
            'reason'      => 'Opname produk 1.',
        ]);

        // Penyesuaian untuk produk 2
        $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $product2->id,
            'stock_after' => 25,
            'reason'      => 'Opname produk 2.',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/stock-adjustments?product_id=' . $this->product->id);

        $response->assertStatus(200);

        $adjustmentsProduct1 = StockAdjustment::where('product_id', $this->product->id)->get();
        $this->assertCount(1, $adjustmentsProduct1);
        $this->assertEquals($this->product->id, $adjustmentsProduct1->first()->product_id);
    }

    /**
     * @test
     * Filter riwayat penyesuaian berdasarkan rentang tanggal menghasilkan data yang benar.
     * Requirements: 7.4
     */
    public function filter_riwayat_penyesuaian_berdasarkan_rentang_tanggal_menghasilkan_data_benar(): void
    {
        // Record penyesuaian lawas (30 hari lalu) — langsung insert untuk bypass validasi tanggal
        StockAdjustment::create([
            'product_id'   => $this->product->id,
            'user_id'      => $this->admin->id,
            'stock_before' => 60,
            'stock_after'  => 50,
            'difference'   => -10,
            'reason'       => 'Opname bulan lalu.',
            'adjusted_at'  => now()->subDays(30),
        ]);

        // Record penyesuaian terbaru (hari ini)
        $this->actingAs($this->admin)->post('/admin/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => 45,
            'reason'      => 'Opname hari ini.',
        ]);

        $startDate = now()->subDays(7)->toDateString();
        $endDate   = now()->toDateString();

        $response = $this->actingAs($this->admin)
            ->get("/admin/stock-adjustments?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);

        // Hanya record dalam rentang 7 hari terakhir
        $filteredCount = StockAdjustment::whereBetween('adjusted_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ])->count();

        $this->assertEquals(1, $filteredCount, 'Hanya penyesuaian dalam rentang tanggal yang harus ditampilkan.');
    }

    // -------------------------------------------------------------------------
    // Otorisasi
    // -------------------------------------------------------------------------

    /**
     * @test
     * Pengguna dengan role kasir tidak bisa mengakses penyesuaian stok —
     * diarahkan ke dasbor kasir oleh CheckRole middleware.
     * Requirements: 7.6, 1.11
     */
    public function kasir_tidak_bisa_mengakses_penyesuaian_stok(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/admin/stock-adjustments');

        // CheckRole middleware mengarahkan ke dasbor sesuai peran, bukan 403
        $response->assertRedirect('/kasir/dashboard');
        $response->assertSessionHas('error');
    }

    /**
     * @test
     * Pengguna dengan role gudang bisa melakukan penyesuaian stok via rute gudang.
     * Requirements: 7.1, 1.11
     */
    public function gudang_bisa_melakukan_penyesuaian_stok(): void
    {
        $gudang = User::factory()->gudang()->create();

        $response = $this->actingAs($gudang)->post('/gudang/stock-adjustments', [
            'product_id'  => $this->product->id,
            'stock_after' => 40,
            'reason'      => 'Opname stok oleh gudang.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals(40, $this->product->current_stock);
    }

    // -------------------------------------------------------------------------
    // Edit & Update Penyesuaian Stok
    // -------------------------------------------------------------------------

    /**
     * @test
     * Admin dapat melihat form edit penyesuaian stok.
     */
    public function admin_dapat_melihat_halaman_edit_penyesuaian_stok(): void
    {
        $adjustment = StockAdjustment::create([
            'product_id'   => $this->product->id,
            'user_id'      => $this->admin->id,
            'stock_before' => 50,
            'stock_after'  => 40,
            'difference'   => -10,
            'reason'       => 'Opname awal.',
            'adjusted_at'  => now(),
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/stock-adjustments/{$adjustment->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Penyesuaian Stok');
        $response->assertSee('Opname awal.');
    }

    /**
     * @test
     * Admin berhasil memperbarui penyesuaian stok dan stok produk tersinkronisasi.
     */
    public function admin_berhasil_memperbarui_penyesuaian_stok_dan_menyesuaikan_stok_produk(): void
    {
        // Kondisi awal: produk stok 50 disesuaikan jadi 40 (difference = -10)
        $this->product->update(['current_stock' => 40]);
        $adjustment = StockAdjustment::create([
            'product_id'   => $this->product->id,
            'user_id'      => $this->admin->id,
            'stock_before' => 50,
            'stock_after'  => 40,
            'difference'   => -10,
            'reason'       => 'Opname awal.',
            'adjusted_at'  => now(),
        ]);

        // Edit koreksi fisik: ternyata stok aktual adalah 45 (bukan 40)
        $response = $this->actingAs($this->admin)->put("/admin/stock-adjustments/{$adjustment->id}", [
            'product_id'  => $this->product->id,
            'stock_after' => 45,
            'reason'      => 'Koreksi salah hitung opname.',
        ]);

        $response->assertRedirect('/admin/stock-adjustments');
        $response->assertSessionHas('success');

        // Difference baru: 45 - 50 = -5
        $adjustment->refresh();
        $this->assertEquals(45, $adjustment->stock_after);
        $this->assertEquals(-5, $adjustment->difference);
        $this->assertEquals('Koreksi salah hitung opname.', $adjustment->reason);

        // Stok produk: 40 + (45 - 40) = 45
        $this->product->refresh();
        $this->assertEquals(45, $this->product->current_stock);
    }

    /**
     * @test
     * Pembaruan penyesuaian stok yang menyebabkan stok produk negatif ditolak.
     */
    public function pembaruan_penyesuaian_stok_yang_menyebabkan_stok_negatif_ditolak(): void
    {
        // Kondisi: produk stok 50 disesuaikan jadi 10 (difference = -40)
        // Kemudian terjual 9, sehingga stok saat ini tinggal 1
        $this->product->update(['current_stock' => 1]);
        $adjustment = StockAdjustment::create([
            'product_id'   => $this->product->id,
            'user_id'      => $this->admin->id,
            'stock_before' => 50,
            'stock_after'  => 10,
            'difference'   => -40,
            'reason'       => 'Opname awal.',
            'adjusted_at'  => now(),
        ]);

        // Coba edit stock_after jadi 5 (delta = 5 - 10 = -5).
        // Stok produk saat ini 1 + (-5) = -4 (negatif!)
        $response = $this->actingAs($this->admin)->put("/admin/stock-adjustments/{$adjustment->id}", [
            'product_id'  => $this->product->id,
            'stock_after' => 5,
            'reason'      => 'Koreksi opname.',
        ]);

        $response->assertSessionHasErrors(['stock_after']);

        // Pastikan stok produk dan adjustment tidak berubah
        $this->product->refresh();
        $this->assertEquals(1, $this->product->current_stock);
        $adjustment->refresh();
        $this->assertEquals(10, $adjustment->stock_after);
    }

    // -------------------------------------------------------------------------
    // Hapus Penyesuaian Stok
    // -------------------------------------------------------------------------

    /**
     * @test
     * Admin berhasil menghapus penyesuaian stok dan stok produk kembali ke sebelum penyesuaian.
     */
    public function admin_berhasil_menghapus_penyesuaian_stok_dan_mengembalikan_stok_produk(): void
    {
        // Kondisi: produk stok awal 50, disesuaikan jadi 35 (difference = -15)
        $this->product->update(['current_stock' => 35]);
        $adjustment = StockAdjustment::create([
            'product_id'   => $this->product->id,
            'user_id'      => $this->admin->id,
            'stock_before' => 50,
            'stock_after'  => 35,
            'difference'   => -15,
            'reason'       => 'Opname salah catat.',
            'adjusted_at'  => now(),
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/stock-adjustments/{$adjustment->id}");

        $response->assertRedirect('/admin/stock-adjustments');
        $response->assertSessionHas('success');

        // Record adjustment terhapus
        $this->assertDatabaseMissing('stock_adjustments', ['id' => $adjustment->id]);

        // Stok produk dikembalikan: 35 - (-15) = 50
        $this->product->refresh();
        $this->assertEquals(50, $this->product->current_stock);
    }

    /**
     * @test
     * Penghapusan penyesuaian stok yang menyebabkan stok negatif ditolak dengan flash error.
     */
    public function penghapusan_penyesuaian_stok_yang_menyebabkan_stok_negatif_gagal_dengan_pesan_error(): void
    {
        // Penyesuaian stok menambah stok dari 50 ke 70 (difference = +20)
        // Kemudian terjual 65, sehingga stok saat ini sisa 5.
        // Jika penyesuaian dihapus, stok harus berkurang 20: 5 - 20 = -15 (negatif!)
        $this->product->update(['current_stock' => 5]);
        $adjustment = StockAdjustment::create([
            'product_id'   => $this->product->id,
            'user_id'      => $this->admin->id,
            'stock_before' => 50,
            'stock_after'  => 70,
            'difference'   => 20,
            'reason'       => 'Penyesuaian stok naik.',
            'adjusted_at'  => now(),
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/stock-adjustments/{$adjustment->id}");

        $response->assertRedirect('/admin/stock-adjustments');
        $response->assertSessionHas('error');

        // Adjustment tidak terhapus
        $this->assertDatabaseHas('stock_adjustments', ['id' => $adjustment->id]);

        // Stok produk tetap 5
        $this->product->refresh();
        $this->assertEquals(5, $this->product->current_stock);
    }
}
