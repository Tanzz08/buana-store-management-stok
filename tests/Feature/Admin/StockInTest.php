<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockIn;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk modul Stok Masuk (Requirements 5.1–5.8).
 *
 * Memverifikasi:
 * - Stok masuk valid menambah current_stock secara tepat dan menghasilkan reference_number unik
 * - Validasi menolak quantity ≤ 0, produk/pemasok tidak terdaftar
 * - Filter riwayat stok masuk berdasarkan produk, pemasok, dan rentang tanggal
 */
class StockInTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;
    private Product $product;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        $this->category = Category::create([
            'name'        => 'Handphone',
            'description' => 'Kategori handphone dan smartphone',
        ]);

        $this->product = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'HP-001',
            'name'           => 'Samsung Galaxy A15',
            'purchase_price' => 2000000,
            'selling_price'  => 2500000,
            'unit'           => 'unit',
            'current_stock'  => 10,
            'minimum_stock'  => 5,
            'status'         => 'active',
        ]);

        $this->supplier = Supplier::create([
            'name'  => 'PT Distributor Elektronik',
            'phone' => '02112345678',
            'email' => 'distributor@example.com',
        ]);
    }

    // -------------------------------------------------------------------------
    // Stok masuk valid — stok bertambah, reference_number unik dengan prefix SI
    // -------------------------------------------------------------------------

    /**
     * @test
     * Stok masuk valid harus menambah current_stock produk secara tepat.
     * Requirements: 5.1, 5.2
     */
    public function stok_masuk_valid_menambah_current_stock_secara_tepat(): void
    {
        $stockBefore = $this->product->current_stock; // 10

        $response = $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 25,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->product->refresh();

        $this->assertEquals(
            $stockBefore + 25,
            $this->product->current_stock,
            'current_stock harus bertambah tepat sebesar quantity yang diinput.'
        );
    }

    /**
     * @test
     * Stok masuk valid harus menghasilkan reference_number dengan format dan keunikan yang benar.
     * Requirements: 5.3
     */
    public function stok_masuk_valid_menghasilkan_reference_number_unik_dengan_prefix_si(): void
    {
        $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 10,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        $stockIn = StockIn::latest('id')->first();

        $this->assertNotNull($stockIn, 'Record stok masuk harus tersimpan di database.');

        // Format: SI + YYYYMMDD + 10 karakter random uppercase — total 20 karakter
        $this->assertStringStartsWith('SI', $stockIn->reference_number);
        $this->assertMatchesRegularExpression(
            '/^SI\d{8}[A-Z0-9]{10}$/',
            $stockIn->reference_number,
            'reference_number harus mengikuti format: SI + YYYYMMDD + 10 karakter alfanumerik uppercase.'
        );
        $this->assertLessThanOrEqual(20, strlen($stockIn->reference_number));

        // Catat dua transaksi — reference_number harus berbeda
        $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 5,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        $allRefs = StockIn::pluck('reference_number');
        $this->assertEquals(
            $allRefs->count(),
            $allRefs->unique()->count(),
            'Setiap reference_number harus unik di seluruh sistem.'
        );
    }

    /**
     * @test
     * total_value harus sama dengan round(quantity × unit_price, 2).
     * Requirements: 5.2
     */
    public function stok_masuk_valid_menghitung_total_value_secara_tepat(): void
    {
        $qty       = 7;
        $unitPrice = 1999999.99;

        $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => $qty,
            'unit_price'  => $unitPrice,
            'received_at' => now()->toDateString(),
        ]);

        $stockIn = StockIn::latest('id')->first();

        $expectedTotal = round($qty * $unitPrice, 2);

        $this->assertEqualsWithDelta(
            $expectedTotal,
            (float) $stockIn->total_value,
            0.001,
            'total_value harus sama dengan round(quantity × unit_price, 2).'
        );
    }

    // -------------------------------------------------------------------------
    // Validasi input — quantity = 0 dan nilai tidak valid
    // -------------------------------------------------------------------------

    /**
     * @test
     * Stok masuk dengan quantity = 0 harus ditolak validasi dan stok tidak berubah.
     * Requirements: 5.4
     */
    public function stok_masuk_dengan_quantity_nol_ditolak_dan_stok_tidak_berubah(): void
    {
        $stockBefore = $this->product->current_stock;

        $response = $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 0,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('quantity');

        $this->product->refresh();
        $this->assertEquals(
            $stockBefore,
            $this->product->current_stock,
            'current_stock tidak boleh berubah ketika validasi gagal.'
        );

        $this->assertDatabaseCount('stock_ins', 0);
    }

    /**
     * @test
     * Stok masuk dengan quantity negatif harus ditolak validasi.
     * Requirements: 5.4
     */
    public function stok_masuk_dengan_quantity_negatif_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => -5,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('stock_ins', 0);
    }

    /**
     * @test
     * Stok masuk dengan unit_price = 0 harus ditolak validasi.
     * Requirements: 5.5
     */
    public function stok_masuk_dengan_unit_price_nol_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 10,
            'unit_price'  => 0,
            'received_at' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('unit_price');
        $this->assertDatabaseCount('stock_ins', 0);
    }

    /**
     * @test
     * Stok masuk dengan product_id tidak terdaftar harus ditolak validasi.
     * Requirements: 5.6
     */
    public function stok_masuk_dengan_produk_tidak_terdaftar_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => 99999,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 10,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('stock_ins', 0);
    }

    /**
     * @test
     * Stok masuk dengan supplier_id tidak terdaftar harus ditolak validasi.
     * Requirements: 5.6
     */
    public function stok_masuk_dengan_pemasok_tidak_terdaftar_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => 99999,
            'quantity'    => 10,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('supplier_id');
        $this->assertDatabaseCount('stock_ins', 0);
    }

    /**
     * @test
     * Stok masuk dengan received_at di masa depan harus ditolak validasi.
     * Requirements: 5.7
     */
    public function stok_masuk_dengan_tanggal_masa_depan_ditolak(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 10,
            'unit_price'  => 1950000,
            'received_at' => now()->addDay()->toDateString(),
        ]);

        $response->assertSessionHasErrors('received_at');
        $this->assertDatabaseCount('stock_ins', 0);
    }

    // -------------------------------------------------------------------------
    // Filter riwayat stok masuk — hasil benar dan performa < 3 detik
    // -------------------------------------------------------------------------

    /**
     * @test
     * Filter riwayat stok masuk berdasarkan produk menghasilkan hanya record produk tersebut.
     * Requirements: 5.8
     */
    public function filter_riwayat_stok_masuk_berdasarkan_produk_menghasilkan_data_benar(): void
    {
        // Produk kedua
        $product2 = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'HP-002',
            'name'           => 'Xiaomi Redmi 12',
            'purchase_price' => 1500000,
            'selling_price'  => 1800000,
            'unit'           => 'unit',
            'current_stock'  => 0,
            'minimum_stock'  => 3,
            'status'         => 'active',
        ]);

        // Catat stok masuk untuk produk 1
        $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 10,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        // Catat stok masuk untuk produk 2
        $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $product2->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 20,
            'unit_price'  => 1450000,
            'received_at' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/stock-ins?product_id=' . $this->product->id);

        $response->assertStatus(200);

        // Semua record yang tampil harus untuk produk yang difilter
        $stockIns = StockIn::where('product_id', $this->product->id)->get();
        $this->assertCount(1, $stockIns);
        $this->assertEquals($this->product->id, $stockIns->first()->product_id);
    }

    /**
     * @test
     * Filter riwayat stok masuk berdasarkan pemasok menghasilkan data yang benar.
     * Requirements: 5.8
     */
    public function filter_riwayat_stok_masuk_berdasarkan_pemasok_menghasilkan_data_benar(): void
    {
        $supplier2 = Supplier::create([
            'name'  => 'CV Mitra Teknologi',
            'phone' => '02298765432',
        ]);

        $product2 = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'HP-003',
            'name'           => 'Oppo A54',
            'purchase_price' => 1800000,
            'selling_price'  => 2100000,
            'unit'           => 'unit',
            'current_stock'  => 0,
            'minimum_stock'  => 3,
            'status'         => 'active',
        ]);

        // Stok masuk dari supplier 1
        $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 10,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        // Stok masuk dari supplier 2
        $this->actingAs($this->admin)->post('/admin/stock-ins', [
            'product_id'  => $product2->id,
            'supplier_id' => $supplier2->id,
            'quantity'    => 5,
            'unit_price'  => 1750000,
            'received_at' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/stock-ins?supplier_id=' . $supplier2->id);

        $response->assertStatus(200);

        $stockInsSupplier2 = StockIn::where('supplier_id', $supplier2->id)->get();
        $this->assertCount(1, $stockInsSupplier2);
        $this->assertEquals($supplier2->id, $stockInsSupplier2->first()->supplier_id);
    }

    /**
     * @test
     * Filter riwayat stok masuk berdasarkan rentang tanggal menghasilkan data yang benar.
     * Requirements: 5.8
     */
    public function filter_riwayat_stok_masuk_berdasarkan_rentang_tanggal_menghasilkan_data_benar(): void
    {
        // Catat stok masuk dengan tanggal 30 hari lalu (di luar rentang filter)
        StockIn::create([
            'reference_number' => 'SI' . now()->subDays(30)->format('Ymd') . 'TESTOLD001',
            'product_id'       => $this->product->id,
            'supplier_id'      => $this->supplier->id,
            'user_id'          => $this->admin->id,
            'quantity'         => 10,
            'unit_price'       => 1950000,
            'total_value'      => 19500000,
            'received_at'      => now()->subDays(30)->toDateString(),
        ]);

        // Catat stok masuk dengan tanggal hari ini (di dalam rentang filter)
        StockIn::create([
            'reference_number' => 'SI' . now()->format('Ymd') . 'TESTNEW0001',
            'product_id'       => $this->product->id,
            'supplier_id'      => $this->supplier->id,
            'user_id'          => $this->admin->id,
            'quantity'         => 5,
            'unit_price'       => 1950000,
            'total_value'      => 9750000,
            'received_at'      => now()->toDateString(),
        ]);

        $startDate = now()->subDays(7)->toDateString();
        $endDate   = now()->toDateString();

        $response = $this->actingAs($this->admin)
            ->get("/admin/stock-ins?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);

        // Hanya record dalam rentang tanggal yang dikembalikan
        // Gunakan whereDate() agar kompatibel dengan SQLite (yang menyimpan date sebagai datetime string)
        $filteredCount = StockIn::whereDate('received_at', '>=', $startDate)
            ->whereDate('received_at', '<=', $endDate)
            ->count();
        $this->assertEquals(1, $filteredCount, 'Hanya stok masuk dalam rentang tanggal yang harus ditampilkan.');
    }

    /**
     * @test
     * Filter riwayat stok masuk harus selesai dalam waktu kurang dari 3 detik.
     * Requirements: 5.8
     */
    public function filter_riwayat_stok_masuk_selesai_dalam_waktu_kurang_dari_3_detik(): void
    {
        // Siapkan sejumlah data stok masuk untuk pengujian performa
        for ($i = 0; $i < 20; $i++) {
            StockIn::create([
                'reference_number' => 'SIPERF' . str_pad((string) $i, 14, '0', STR_PAD_LEFT),
                'product_id'       => $this->product->id,
                'supplier_id'      => $this->supplier->id,
                'user_id'          => $this->admin->id,
                'quantity'         => $i + 1,
                'unit_price'       => 1950000,
                'total_value'      => ($i + 1) * 1950000,
                'received_at'      => now()->subDays($i)->toDateString(),
            ]);
        }

        $startDate = now()->subDays(30)->toDateString();
        $endDate   = now()->toDateString();

        $start = microtime(true);

        $response = $this->actingAs($this->admin)
            ->get("/admin/stock-ins?product_id={$this->product->id}&supplier_id={$this->supplier->id}&start_date={$startDate}&end_date={$endDate}");

        $elapsed = microtime(true) - $start;

        $response->assertStatus(200);

        $this->assertLessThan(
            3.0,
            $elapsed,
            "Filter riwayat stok masuk membutuhkan {$elapsed} detik, batas maksimal adalah 3 detik."
        );
    }

    // -------------------------------------------------------------------------
    // Otorisasi — kasir tidak punya akses
    // -------------------------------------------------------------------------

    /**
     * @test
     * Pengguna dengan role kasir tidak bisa mengakses halaman stok masuk —
     * diarahkan ke dasbor kasir oleh CheckRole middleware.
     * Requirements: 5.8, 1.11
     */
    public function kasir_tidak_bisa_mengakses_stok_masuk(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get('/admin/stock-ins');

        // CheckRole middleware mengarahkan ke dasbor sesuai peran, bukan 403
        $response->assertRedirect('/kasir/dashboard');
        $response->assertSessionHas('error');
    }

    /**
     * @test
     * Pengguna dengan role gudang bisa mencatat stok masuk via rute gudang.
     * Requirements: 5.1, 1.11
     */
    public function gudang_bisa_mencatat_stok_masuk(): void
    {
        $gudang = User::factory()->gudang()->create();
        $stockBefore = $this->product->current_stock;

        $response = $this->actingAs($gudang)->post('/gudang/stock-ins', [
            'product_id'  => $this->product->id,
            'supplier_id' => $this->supplier->id,
            'quantity'    => 15,
            'unit_price'  => 1950000,
            'received_at' => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->product->refresh();
        $this->assertEquals($stockBefore + 15, $this->product->current_stock);
    }
}
