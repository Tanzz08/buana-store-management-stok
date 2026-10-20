<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductChangeLog;
use App\Models\Supplier;
use App\Models\StockIn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Feature tests untuk modul Manajemen Produk dan Pemasok.
 *
 * Validates: Requirements 3.1–3.9, 4.3–4.8
 */
class ProductSupplierTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $kasir;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->kasir = User::factory()->kasir()->create();

        $this->category = Category::create([
            'name'        => 'Handphone',
            'description' => 'Kategori untuk handphone',
        ]);
    }

    // =========================================================================
    // PRODUK — Tambah produk baru
    // =========================================================================

    /**
     * Req 3.2: Produk baru harus disimpan dengan stok awal 0 dan status active.
     */
    public function test_tambah_produk_sku_unik_menghasilkan_stok_awal_nol_dan_status_active(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.store'), [
                'sku'            => 'SKU-001',
                'name'           => 'Samsung Galaxy A15',
                'category_id'    => $this->category->id,
                'purchase_price' => 2000000,
                'selling_price'  => 2500000,
                'unit'           => 'unit',
                'minimum_stock'  => 5,
            ]);

        $response->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'sku'           => 'SKU-001',
            'name'          => 'Samsung Galaxy A15',
            'current_stock' => 0,
            'status'        => 'active',
        ]);
    }

    /**
     * Req 3.5: Update produk harus mencatat riwayat perubahan ke product_change_logs
     * dengan before_data, after_data, user_id, dan created_at.
     */
    public function test_update_produk_mencatat_product_change_log(): void
    {
        $product = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'SKU-BEFORE',
            'name'           => 'Nama Lama',
            'purchase_price' => 1000000,
            'selling_price'  => 1200000,
            'unit'           => 'unit',
            'current_stock'  => 0,
            'minimum_stock'  => 3,
            'status'         => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), [
                'name'           => 'Nama Baru',
                'category_id'    => $this->category->id,
                'purchase_price' => 1100000,
                'selling_price'  => 1300000,
                'unit'           => 'unit',
                'minimum_stock'  => 3,
            ]);

        $response->assertRedirect(route('admin.products.index'));

        $log = ProductChangeLog::where('product_id', $product->id)->first();

        $this->assertNotNull($log, 'product_change_logs harus memiliki entri setelah update.');
        $this->assertEquals($this->admin->id, $log->user_id);
        $this->assertIsArray($log->before_data);
        $this->assertIsArray($log->after_data);
        $this->assertEquals('Nama Lama', $log->before_data['name']);
        $this->assertEquals('Nama Baru', $log->after_data['name']);
        $this->assertNotNull($log->created_at);
    }

    /**
     * Req 3.5: SKU bersifat permanen — field sku diabaikan saat update,
     * SKU produk tidak boleh berubah.
     */
    public function test_update_produk_mengabaikan_field_sku(): void
    {
        $product = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'SKU-PERMANEN',
            'name'           => 'Produk Awal',
            'purchase_price' => 500000,
            'selling_price'  => 600000,
            'unit'           => 'unit',
            'current_stock'  => 0,
            'minimum_stock'  => 2,
            'status'         => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.products.update', $product), [
                'sku'            => 'SKU-BARU-COBA', // seharusnya diabaikan
                'name'           => 'Produk Diperbarui',
                'category_id'    => $this->category->id,
                'purchase_price' => 500000,
                'selling_price'  => 600000,
                'unit'           => 'unit',
                'minimum_stock'  => 2,
            ]);

        $response->assertRedirect(route('admin.products.index'));

        // SKU tidak boleh berubah
        $this->assertDatabaseHas('products', [
            'id'  => $product->id,
            'sku' => 'SKU-PERMANEN',
        ]);
        $this->assertDatabaseMissing('products', [
            'id'  => $product->id,
            'sku' => 'SKU-BARU-COBA',
        ]);
    }

    /**
     * Req 3.7: Produk yang dinonaktifkan tidak boleh muncul di endpoint daftar
     * produk aktif yang digunakan oleh form penjualan kasir.
     *
     * Endpoint GET /api/products/search tanpa query keyword mengembalikan semua
     * produk aktif. Pengujian ini memverifikasi bahwa produk dengan status
     * 'inactive' tidak masuk ke dalam daftar tersebut.
     */
    public function test_nonaktifkan_produk_tidak_muncul_pada_daftar_produk_aktif(): void
    {
        $active = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'SKU-ACTIVE',
            'name'           => 'Aksesori Aktif',
            'purchase_price' => 100000,
            'selling_price'  => 120000,
            'unit'           => 'unit',
            'current_stock'  => 10,
            'minimum_stock'  => 2,
            'status'         => 'active',
        ]);

        $inactive = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'SKU-INACTIVE',
            'name'           => 'Aksesori Nonaktif',
            'purchase_price' => 200000,
            'selling_price'  => 240000,
            'unit'           => 'unit',
            'current_stock'  => 5,
            'minimum_stock'  => 2,
            'status'         => 'inactive',
        ]);

        // Tanpa keyword: endpoint mengembalikan semua produk aktif (tidak ada ILIKE)
        $response = $this->actingAs($this->kasir)
            ->getJson('/api/products/search');

        $response->assertStatus(200);

        $ids = collect($response->json())->pluck('id');
        $this->assertContains($active->id, $ids->toArray(),
            'Produk aktif harus muncul di daftar produk aktif.');
        $this->assertNotContains($inactive->id, $ids->toArray(),
            'Produk tidak aktif tidak boleh muncul di daftar produk aktif.');
    }

    /**
     * Req 3.8, 3.9: CSV import dengan baris campuran — hanya baris valid disimpan,
     * jumlah failures sesuai dengan baris tidak valid.
     */
    public function test_csv_import_baris_campuran_hanya_simpan_baris_valid(): void
    {
        // Buat kategori tambahan untuk digunakan dalam CSV
        Category::create(['name' => 'Aksesoris']);

        // CSV dengan 2 baris valid dan 2 baris tidak valid
        $csvContent = implode("\n", [
            'sku,nama,kategori,harga_beli,harga_jual,satuan,stok_minimum',
            // baris valid #1
            'CSV-001,Kabel USB Tipe C,Aksesoris,15000,25000,pcs,5',
            // baris valid #2
            'CSV-002,Charger Original,Aksesoris,50000,75000,pcs,3',
            // baris tidak valid: harga_beli = 0
            'CSV-003,Produk Invalid Harga,Aksesoris,0,10000,pcs,2',
            // baris tidak valid: kategori tidak ada
            'CSV-004,Produk Invalid Kategori,KategoriTidakAda,10000,15000,pcs,1',
        ]);

        $file = UploadedFile::fake()->createWithContent('produk.csv', $csvContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.products.import'), [
                'csv_file' => $file,
            ]);

        $response->assertRedirect(route('admin.products.import.form'));
        $response->assertSessionHas('import_success', 2);

        $failures = $response->baseResponse->getSession()->get('import_failures');
        $this->assertCount(2, $failures, 'Harus ada tepat 2 baris gagal.');

        $this->assertDatabaseHas('products', ['sku' => 'CSV-001']);
        $this->assertDatabaseHas('products', ['sku' => 'CSV-002']);
        $this->assertDatabaseMissing('products', ['sku' => 'CSV-003']);
        $this->assertDatabaseMissing('products', ['sku' => 'CSV-004']);
    }

    // =========================================================================
    // PEMASOK — Validasi dan penghapusan
    // =========================================================================

    /**
     * Req 4.4: Nomor telepon dengan kurang dari 7 digit harus gagal validasi
     * dengan pesan error yang mengindikasikan format tidak valid.
     */
    public function test_tambah_pemasok_nomor_telepon_kurang_dari_7_digit_gagal_validasi(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.suppliers.store'), [
                'name'  => 'Pemasok Baru',
                'phone' => '123456', // hanya 6 digit, harus gagal
                'email' => 'pemasok@example.com',
            ]);

        $response->assertSessionHasErrors(['phone']);

        $errors = $response->getSession()->get('errors')->default->get('phone');
        $this->assertNotEmpty($errors, 'Harus ada pesan error untuk field phone.');
        $this->assertStringContainsString('7', implode(' ', $errors),
            'Pesan error harus menyebutkan batas 7 digit.');

        $this->assertDatabaseMissing('suppliers', ['name' => 'Pemasok Baru']);
    }

    /**
     * Req 4.4: Nomor telepon yang valid (7–15 digit) harus diterima.
     */
    public function test_tambah_pemasok_nomor_telepon_valid_berhasil(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.suppliers.store'), [
                'name'  => 'Distributor Resmi',
                'phone' => '0812345678', // 10 digit, valid
                'email' => 'distributor@example.com',
            ]);

        $response->assertRedirect(route('admin.suppliers.index'));
        $this->assertDatabaseHas('suppliers', [
            'name'  => 'Distributor Resmi',
            'phone' => '0812345678',
        ]);
    }

    /**
     * Req 4.8: Menghapus pemasok yang memiliki riwayat stok masuk harus
     * menghasilkan redirect dengan flash error, bukan penghapusan.
     */
    public function test_hapus_pemasok_dengan_riwayat_stok_masuk_menghasilkan_flash_error(): void
    {
        $supplier = Supplier::create([
            'name'  => 'Pemasok Dengan Riwayat',
            'phone' => '0811111111',
        ]);

        $product = Product::create([
            'category_id'    => $this->category->id,
            'sku'            => 'SKU-SUPP',
            'name'           => 'Produk Untuk StockIn',
            'purchase_price' => 100000,
            'selling_price'  => 150000,
            'unit'           => 'unit',
            'current_stock'  => 10,
            'minimum_stock'  => 2,
            'status'         => 'active',
        ]);

        // Buat riwayat stok masuk yang dikaitkan ke supplier
        StockIn::create([
            'reference_number' => 'SI20240101ABCDEFGHIJ',
            'product_id'       => $product->id,
            'supplier_id'      => $supplier->id,
            'user_id'          => $this->admin->id,
            'quantity'         => 10,
            'unit_price'       => 100000,
            'total_value'      => 1000000,
            'received_at'      => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.suppliers.destroy', $supplier));

        $response->assertRedirect(route('admin.suppliers.index'));
        $response->assertSessionHas('error');

        // Supplier harus tetap ada di database
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id]);
    }
}
