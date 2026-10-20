<?php

namespace Tests\Feature\Kasir;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk modul Penjualan (Kasir) — Task 23.5
 *
 * Mencakup:
 * - Transaksi valid multi-item → stok setiap produk berkurang atomik, invoice_number unik (prefix INV)
 * - Qty melebihi stok → HTTP 422/redirect, pesan error menyebut nama produk dan stok tersedia, semua stok rollback
 * - Produk nonaktif dimasukkan ke cart → validasi gagal
 * - Download PDF invoice → Content-Type: application/pdf, ukuran file > 0
 * - Filter riwayat penjualan (tanggal + kasir) → hasil benar, maks 100 per halaman
 *
 * Requirements: 6.1–6.9
 */
class SaleTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Buat kategori uji coba.
     */
    private function buatKategori(string $nama = 'Handphone'): Category
    {
        return Category::create([
            'name'        => $nama,
            'description' => 'Kategori uji coba',
        ]);
    }

    /**
     * Buat produk aktif dengan stok tertentu.
     */
    private function buatProdukAktif(
        Category $kategori,
        string $nama,
        string $sku,
        int $stok = 50,
        float $hargaBeli = 1000000,
        float $hargaJual = 1200000,
    ): Product {
        return Product::create([
            'category_id'    => $kategori->id,
            'sku'            => $sku,
            'name'           => $nama,
            'purchase_price' => $hargaBeli,
            'selling_price'  => $hargaJual,
            'unit'           => 'unit',
            'current_stock'  => $stok,
            'minimum_stock'  => 5,
            'status'         => 'active',
        ]);
    }

    /**
     * Buat produk nonaktif.
     */
    private function buatProdukNonaktif(
        Category $kategori,
        string $nama = 'Produk Nonaktif',
        string $sku = 'SKU-NONAKTIF',
    ): Product {
        return Product::create([
            'category_id'    => $kategori->id,
            'sku'            => $sku,
            'name'           => $nama,
            'purchase_price' => 500000,
            'selling_price'  => 600000,
            'unit'           => 'unit',
            'current_stock'  => 100,
            'minimum_stock'  => 5,
            'status'         => 'inactive',
        ]);
    }

    /**
     * Buat kasir.
     */
    private function buatKasir(): User
    {
        return User::factory()->kasir()->create();
    }

    // =========================================================================
    // Requirement 6.1, 6.3, 6.4 — Transaksi valid multi-item → stok berkurang atomik
    // =========================================================================

    /** @test */
    public function transaksi_valid_multi_item_stok_setiap_produk_berkurang_atomik(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori();

        $produk1 = $this->buatProdukAktif($kategori, 'iPhone 15', 'SKU-IP15', stok: 20, hargaJual: 12000000);
        $produk2 = $this->buatProdukAktif($kategori, 'Samsung S24', 'SKU-SS24', stok: 15, hargaJual: 10000000);

        $response = $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk1->id, 'quantity' => 3],
                ['product_id' => $produk2->id, 'quantity' => 2],
            ],
        ]);

        // Stok produk 1 berkurang 3
        $this->assertDatabaseHas('products', [
            'id'            => $produk1->id,
            'current_stock' => 17, // 20 - 3
        ]);

        // Stok produk 2 berkurang 2
        $this->assertDatabaseHas('products', [
            'id'            => $produk2->id,
            'current_stock' => 13, // 15 - 2
        ]);

        // Sale record tersimpan
        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('sale_items', 2);

        // Redirect ke halaman detail transaksi
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function invoice_number_dimulai_dengan_prefix_inv(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Aksesoris');
        $produk   = $this->buatProdukAktif($kategori, 'Casing HP', 'SKU-CASING', stok: 30, hargaJual: 50000);

        $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 1],
            ],
        ]);

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertStringStartsWith('INV', $sale->invoice_number);
    }

    /** @test */
    public function dua_transaksi_berurutan_menghasilkan_invoice_number_unik(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Kartu SIM');
        $produk   = $this->buatProdukAktif($kategori, 'SIM Card', 'SKU-SIM', stok: 100, hargaJual: 10000);

        // Transaksi pertama
        $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [['product_id' => $produk->id, 'quantity' => 1]],
        ]);

        // Transaksi kedua
        $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [['product_id' => $produk->id, 'quantity' => 1]],
        ]);

        $invoiceNumbers = Sale::pluck('invoice_number');
        $this->assertCount(2, $invoiceNumbers);
        $this->assertEquals(2, $invoiceNumbers->unique()->count(), 'Nomor invoice harus unik');
    }

    /** @test */
    public function total_amount_dihitung_dari_harga_database_bukan_request(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Pulsa');
        $produk   = $this->buatProdukAktif($kategori, 'Pulsa Telkomsel 50rb', 'SKU-P50', stok: 50, hargaJual: 52000);

        $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 3],
            ],
        ]);

        $sale = Sale::first();
        $this->assertNotNull($sale);
        // total = 3 × 52000 = 156000
        $this->assertEquals('156000.00', $sale->total_amount);
    }

    // =========================================================================
    // Requirement 6.2, 6.9 — Stok kurang → error, semua stok rollback
    // =========================================================================

    /** @test */
    public function qty_melebihi_stok_dikembalikan_dengan_pesan_error(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Voucher Data');
        $produk   = $this->buatProdukAktif($kategori, 'Voucher Indosat 10GB', 'SKU-VI10', stok: 3);

        $response = $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 10], // melebihi stok 3
            ],
        ]);

        // Redirect kembali dengan pesan error
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $errorMessage = session('error');
        $this->assertStringContainsString('Voucher Indosat 10GB', $errorMessage);
        $this->assertStringContainsString('3', $errorMessage);
    }

    /** @test */
    public function pesan_error_stok_kurang_menyebut_nama_produk_dan_stok_tersedia(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Handphone');
        $produk   = $this->buatProdukAktif($kategori, 'Oppo Reno 10', 'SKU-OR10', stok: 5);

        $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 99],
            ],
        ]);

        // Format pesan: "Stok {nama} tidak mencukupi. Stok tersedia: {stok} unit."
        $errorMessage = session('error');
        $this->assertStringContainsString('Stok', $errorMessage);
        $this->assertStringContainsString('Oppo Reno 10', $errorMessage);
        $this->assertStringContainsString('5', $errorMessage);
        $this->assertStringContainsString('tidak mencukupi', $errorMessage);
    }

    /** @test */
    public function stok_semua_produk_tidak_berubah_ketika_satu_item_melebihi_stok(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Aksesoris');

        $produk1 = $this->buatProdukAktif($kategori, 'Earphone A', 'SKU-EA', stok: 50);
        $produk2 = $this->buatProdukAktif($kategori, 'Case HP B', 'SKU-CHB', stok: 2); // stok sedikit

        // Item 2 melebihi stok — seluruh transaksi harus di-rollback
        $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk1->id, 'quantity' => 5],
                ['product_id' => $produk2->id, 'quantity' => 10], // melebihi stok 2
            ],
        ]);

        // Stok produk 1 tidak boleh berkurang (rollback)
        $this->assertDatabaseHas('products', [
            'id'            => $produk1->id,
            'current_stock' => 50,
        ]);

        // Stok produk 2 tidak boleh berkurang (rollback)
        $this->assertDatabaseHas('products', [
            'id'            => $produk2->id,
            'current_stock' => 2,
        ]);

        // Tidak ada sale yang tersimpan
        $this->assertDatabaseCount('sales', 0);
    }

    // =========================================================================
    // Requirement 6.1 — Produk nonaktif → validasi gagal (HTTP 422)
    // =========================================================================

    /** @test */
    public function produk_nonaktif_di_cart_gagal_validasi(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Nonaktif');
        $produk   = $this->buatProdukNonaktif($kategori, 'Produk Discontinued', 'SKU-DISC');

        $response = $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 1],
            ],
        ]);

        // Validasi gagal → redirect kembali dengan error
        $response->assertSessionHasErrors('items');

        // Tidak ada transaksi tersimpan
        $this->assertDatabaseCount('sales', 0);

        // Stok tidak berubah
        $this->assertDatabaseHas('products', [
            'id'            => $produk->id,
            'current_stock' => 100,
        ]);
    }

    /** @test */
    public function transaksi_tanpa_items_gagal_validasi(): void
    {
        $kasir = $this->buatKasir();

        $response = $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [],
        ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('sales', 0);
    }

    /** @test */
    public function transaksi_dengan_quantity_nol_gagal_validasi(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Kartu SIM');
        $produk   = $this->buatProdukAktif($kategori, 'SIM Card XL', 'SKU-XLSIM', stok: 20);

        $response = $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 0],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.quantity');
        $this->assertDatabaseCount('sales', 0);
    }

    // =========================================================================
    // Requirement 6.7 — Download PDF invoice → Content-Type: application/pdf
    // =========================================================================

    /** @test */
    public function download_invoice_mengembalikan_response_pdf(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Handphone');
        $produk   = $this->buatProdukAktif($kategori, 'Xiaomi 14', 'SKU-XI14', stok: 10, hargaJual: 8000000);

        // Buat transaksi terlebih dahulu
        $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 1],
            ],
        ]);

        $sale = Sale::with(['items.product', 'user'])->first();
        $this->assertNotNull($sale);

        // Unduh invoice PDF
        $response = $this->actingAs($kasir)->get(route('kasir.sales.invoice', $sale));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'application/pdf',
            $response->headers->get('content-type') ?? ''
        );
    }

    /** @test */
    public function download_invoice_menghasilkan_file_tidak_kosong(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Aksesoris');
        $produk   = $this->buatProdukAktif($kategori, 'Kabel Data USB-C', 'SKU-KD01', stok: 30, hargaJual: 25000);

        $this->actingAs($kasir)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 2],
            ],
        ]);

        $sale = Sale::first();
        $response = $this->actingAs($kasir)->get(route('kasir.sales.invoice', $sale));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertGreaterThan(0, strlen($content), 'Konten PDF tidak boleh kosong');
    }

    /** @test */
    public function kasir_tidak_bisa_mengunduh_invoice_milik_kasir_lain(): void
    {
        $kasir1   = $this->buatKasir();
        $kasir2   = User::factory()->kasir()->create();
        $kategori = $this->buatKategori('Voucher');
        $produk   = $this->buatProdukAktif($kategori, 'Voucher AXIS 5GB', 'SKU-VA5', stok: 50, hargaJual: 15000);

        // Kasir 1 membuat transaksi
        $this->actingAs($kasir1)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 1],
            ],
        ]);

        $sale = Sale::first();

        // Kasir 2 mencoba mengunduh invoice milik kasir 1 → harus ditolak (403)
        $response = $this->actingAs($kasir2)->get(route('kasir.sales.invoice', $sale));
        $response->assertStatus(403);
    }

    // =========================================================================
    // Requirement 6.5, 6.6 — Filter riwayat penjualan (tanggal + kasir)
    // =========================================================================

    /** @test */
    public function filter_tanggal_hanya_menampilkan_transaksi_dalam_rentang_tersebut(): void
    {
        $kasir    = $this->buatKasir();
        $kategori = $this->buatKategori('Filter Test');
        $produk   = $this->buatProdukAktif($kategori, 'Test Produk Filter', 'SKU-FILTER', stok: 200);

        // Buat sale dengan tanggal kemarin
        Sale::create([
            'invoice_number' => 'INV-KEMARIN',
            'user_id'        => $kasir->id,
            'total_amount'   => 50000,
            'sold_at'        => now()->subDay(),
        ]);

        // Buat sale dengan tanggal hari ini
        Sale::create([
            'invoice_number' => 'INV-HARI-INI',
            'user_id'        => $kasir->id,
            'total_amount'   => 75000,
            'sold_at'        => now(),
        ]);

        $today = now()->toDateString();

        $response = $this->actingAs($kasir)->get(route('kasir.sales.index', [
            'start_date' => $today,
            'end_date'   => $today,
        ]));

        $response->assertStatus(200);
        $response->assertSee('INV-HARI-INI');
        $response->assertDontSee('INV-KEMARIN');
    }

    /** @test */
    public function kasir_hanya_melihat_transaksi_miliknya_sendiri(): void
    {
        $kasir1   = $this->buatKasir();
        $kasir2   = User::factory()->kasir()->create();
        $kategori = $this->buatKategori('Milik Kasir');

        // Sale milik kasir1
        Sale::create([
            'invoice_number' => 'INV-KASIR1',
            'user_id'        => $kasir1->id,
            'total_amount'   => 100000,
            'sold_at'        => now(),
        ]);

        // Sale milik kasir2
        Sale::create([
            'invoice_number' => 'INV-KASIR2',
            'user_id'        => $kasir2->id,
            'total_amount'   => 200000,
            'sold_at'        => now(),
        ]);

        // Kasir 1 hanya boleh melihat miliknya
        $response = $this->actingAs($kasir1)->get(route('kasir.sales.index'));

        $response->assertStatus(200);
        $response->assertSee('INV-KASIR1');
        $response->assertDontSee('INV-KASIR2');
    }

    /** @test */
    public function halaman_riwayat_penjualan_memiliki_paginasi_maks_100_per_halaman(): void
    {
        $kasir    = $this->buatKasir();

        // Buat 105 sale untuk kasir ini
        for ($i = 1; $i <= 105; $i++) {
            Sale::create([
                'invoice_number' => sprintf('INV-TEST-%04d', $i),
                'user_id'        => $kasir->id,
                'total_amount'   => 50000,
                'sold_at'        => now(),
            ]);
        }

        $response = $this->actingAs($kasir)->get(route('kasir.sales.index'));

        $response->assertStatus(200);

        // Halaman pertama hanya memuat maks 100 item
        $sales = $response->viewData('sales');
        $this->assertLessThanOrEqual(100, $sales->count(), 'Halaman pertama tidak boleh menampilkan lebih dari 100 item');

        // Total harus ada 105 sale
        $this->assertEquals(105, $sales->total());
    }

    // =========================================================================
    // Requirement 6.1 — Hanya Kasir yang dapat membuat transaksi
    // =========================================================================

    /** @test */
    public function pengguna_tanpa_peran_kasir_tidak_bisa_membuat_transaksi(): void
    {
        $admin    = User::factory()->admin()->create();
        $kategori = $this->buatKategori('Admin Test');
        $produk   = $this->buatProdukAktif($kategori, 'Produk Admin', 'SKU-ADM', stok: 10);

        $response = $this->actingAs($admin)->post(route('kasir.sales.store'), [
            'items' => [
                ['product_id' => $produk->id, 'quantity' => 1],
            ],
        ]);

        // CheckRole middleware mengarahkan pengguna ke dashboard-nya sendiri (302)
        // dan menampilkan pesan 'tidak memiliki izin'
        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('sales', 0);
    }
}
