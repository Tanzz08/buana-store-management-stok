<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    private function buatKategori(string $nama = 'Aksesoris'): Category
    {
        return Category::create([
            'name'        => $nama,
            'description' => 'Kategori aksesoris',
        ]);
    }

    private function buatProduk(
        Category $kategori,
        string $nama,
        string $sku,
        float $hargaBeli,
        float $hargaJual,
        int $stok = 100
    ): Product {
        return Product::create([
            'category_id'    => $kategori->id,
            'sku'            => $sku,
            'name'           => $nama,
            'purchase_price' => $hargaBeli,
            'selling_price'  => $hargaJual,
            'unit'           => 'pcs',
            'current_stock'  => $stok,
            'minimum_stock'  => 5,
            'status'         => 'active',
        ]);
    }

    private function buatTransaksi(
        User $user,
        string $invoiceNumber,
        Carbon $soldAt,
        array $items
    ): Sale {
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item['quantity'] * $item['unit_price'];
        }

        $sale = Sale::create([
            'invoice_number' => $invoiceNumber,
            'user_id'        => $user->id,
            'total_amount'   => $totalAmount,
            'sold_at'        => $soldAt,
        ]);

        foreach ($items as $item) {
            SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $item['product']->id,
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal'   => $item['quantity'] * $item['unit_price'],
            ]);
        }

        return $sale;
    }

    /**
     * @test
     */
    public function tamu_tidak_bisa_mengakses_laporan_penjualan(): void
    {
        $response = $this->get(route('admin.reports.sales'));
        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function non_admin_seperti_kasir_dilarang_mengakses_laporan_penjualan(): void
    {
        $kasir = User::factory()->kasir()->create();

        $response = $this->actingAs($kasir)->get(route('admin.reports.sales'));
        $response->assertRedirect('/kasir/dashboard');
        $response->assertSessionHas('error');
    }

    /**
     * @test
     */
    public function admin_dapat_melihat_laporan_penjualan_lengkap_dengan_keuntungan(): void
    {
        $admin = User::factory()->admin()->create();
        $kasir = User::factory()->kasir()->create();

        $kategori = $this->buatKategori();

        // Produk 1: Beli 10.000, Jual 15.000 -> Laba per unit 5.000
        $p1 = $this->buatProduk($kategori, 'Kabel Data', 'KBL-01', 10000, 15000);
        // Produk 2: Beli 50.000, Jual 80.000 -> Laba per unit 30.000
        $p2 = $this->buatProduk($kategori, 'Charger Cepat', 'CHG-01', 50000, 80000);

        // Transaksi 1 oleh kasir: 2x Kabel Data (30.000) + 1x Charger (80.000) = 110.000
        // Modal = (2*10.000) + (1*50.000) = 70.000
        // Laba = 110.000 - 70.000 = 40.000
        $this->buatTransaksi($kasir, 'INV20261016000001', now(), [
            ['product' => $p1, 'quantity' => 2, 'unit_price' => 15000],
            ['product' => $p2, 'quantity' => 1, 'unit_price' => 80000],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.sales', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date'   => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewIs('admin.reports.sales');

        // Pastikan angka total penjualan (110.000), modal (70.000), dan laba (40.000) tampil di halaman
        $response->assertSee('110.000');
        $response->assertSee('70.000');
        $response->assertSee('40.000');
        $response->assertSee('Kabel Data');
        $response->assertSee('Charger Cepat');
        $response->assertSee('INV20261016000001');
    }

    /**
     * @test
     */
    public function service_menghitung_penjualan_hpp_laba_dan_margin_secara_akurat(): void
    {
        $kasir    = User::factory()->kasir()->create();
        $kategori = $this->buatKategori();

        $produk = $this->buatProduk($kategori, 'Powerbank', 'PB-01', 100000, 150000);

        // Terjual 4 unit: Total Penjualan 600.000, HPP 400.000, Laba 200.000, Margin 33.33%
        $this->buatTransaksi($kasir, 'INV20261016000002', now(), [
            ['product' => $produk, 'quantity' => 4, 'unit_price' => 150000],
        ]);

        $service = app(ReportService::class);
        $report  = $service->getSalesReport(
            now()->startOfMonth()->toDateString(),
            now()->toDateString()
        );

        $this->assertEquals(600000.0, $report['total_revenue']);
        $this->assertEquals(400000.0, $report['total_cogs']);
        $this->assertEquals(200000.0, $report['total_profit']);
        $this->assertEquals(33.33, $report['profit_margin']);
        $this->assertEquals(1, $report['transaction_count']);
        $this->assertEquals(4, $report['total_items_sold']);
        $this->assertEquals(600000.0, $report['average_order_value']);
    }

    /**
     * @test
     */
    public function filter_kasir_dan_rentang_tanggal_berfungsi_dengan_benar(): void
    {
        $admin   = User::factory()->admin()->create();
        $kasir1  = User::factory()->kasir()->create(['name' => 'Budi Kasir']);
        $kasir2  = User::factory()->kasir()->create(['name' => 'Siti Kasir']);
        $kategori = $this->buatKategori();

        $produk = $this->buatProduk($kategori, 'Mouse Wireless', 'MS-01', 50000, 80000);

        // Transaksi 1 oleh kasir1 bulan ini
        $this->buatTransaksi($kasir1, 'INV20261010000001', Carbon::parse('2026-10-10 10:00:00'), [
            ['product' => $produk, 'quantity' => 1, 'unit_price' => 80000],
        ]);

        // Transaksi 2 oleh kasir2 bulan ini
        $this->buatTransaksi($kasir2, 'INV20261012000002', Carbon::parse('2026-10-12 11:00:00'), [
            ['product' => $produk, 'quantity' => 2, 'unit_price' => 80000],
        ]);

        // Transaksi 3 di luar rentang tanggal (bulan lalu)
        $this->buatTransaksi($kasir1, 'INV20260901000001', Carbon::parse('2026-09-01 10:00:00'), [
            ['product' => $produk, 'quantity' => 1, 'unit_price' => 80000],
        ]);

        $service = app(ReportService::class);

        // Filter hanya bulan Oktober dan hanya kasir1
        $reportKasir1 = $service->getSalesReport('2026-10-01', '2026-10-31', $kasir1->id);
        $this->assertEquals(80000.0, $reportKasir1['total_revenue']);
        $this->assertEquals(30000.0, $reportKasir1['total_profit']);
        $this->assertEquals(1, $reportKasir1['transaction_count']);

        // Filter bulan Oktober semua kasir
        $reportSemua = $service->getSalesReport('2026-10-01', '2026-10-31');
        $this->assertEquals(240000.0, $reportSemua['total_revenue']); // 80k + 160k
        $this->assertEquals(90000.0, $reportSemua['total_profit']);   // 30k + 60k
        $this->assertEquals(2, $reportSemua['transaction_count']);
    }

    /**
     * @test
     */
    public function admin_dapat_mengunduh_ekspor_laporan_penjualan_excel(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.reports.export', [
            'type'       => 'sales',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date'   => now()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string) $response->headers->get('content-type'));
    }
}
