<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController as AdminSaleController;
use App\Http\Controllers\Admin\StockAdjustmentController as AdminStockAdjustmentController;
use App\Http\Controllers\Admin\StockInController as AdminStockInController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Gudang\NotificationController as GudangNotificationController;
use App\Http\Controllers\Gudang\ProductController as GudangProductController;
use App\Http\Controllers\Gudang\StockAdjustmentController as GudangStockAdjustmentController;
use App\Http\Controllers\Gudang\StockInController as GudangStockInController;
use App\Http\Controllers\Kasir\SaleController as KasirSaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Selamat Datang
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Route 'dashboard' generik — diperlukan oleh komponen Breeze
| Mengarahkan ke dasbor sesuai peran pengguna yang login
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $role = auth()->user()?->role;
    return match ($role) {
        'admin'  => redirect('/admin/dashboard'),
        'kasir'  => redirect('/kasir/dashboard'),
        'gudang' => redirect('/gudang/dashboard'),
        default  => view('dashboard'),
    };
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Route Admin — hanya untuk pengguna dengan peran 'admin'
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'role:admin'])
    ->name('admin.')
    ->group(function () {

        // Dasbor Admin
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Laporan & Analitik
        Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/stock-mutation', [ReportController::class, 'stockMutation'])->name('reports.stock-mutation');
        Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');

        // Manajemen Pengguna
        Route::resource('users', UserController::class);

        // Manajemen Kategori
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Manajemen Pemasok
        Route::resource('suppliers', SupplierController::class)->except(['show']);

        // Manajemen Produk
        // CATATAN: Route impor harus didefinisikan SEBELUM resource route
        // agar tidak tertabrak route model binding {product}
        Route::get('products/import', [AdminProductController::class, 'importForm'])
            ->name('products.import.form');
        Route::post('products/import', [AdminProductController::class, 'import'])
            ->name('products.import');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::post('products/{product}/activate', [AdminProductController::class, 'activate'])
            ->name('products.activate');
        Route::post('products/{product}/deactivate', [AdminProductController::class, 'deactivate'])
            ->name('products.deactivate');

        // Stok Masuk
        Route::resource('stock-ins', AdminStockInController::class)
            ->only(['index', 'create', 'store', 'show']);

        // Penyesuaian Stok
        Route::resource('stock-adjustments', AdminStockAdjustmentController::class);

        // Penjualan (Admin: baca saja)
        Route::get('sales', [AdminSaleController::class, 'index'])->name('sales.index');
        Route::get('sales/create', [AdminSaleController::class, 'create'])->name('sales.create');
        Route::post('sales', [AdminSaleController::class, 'store'])->name('sales.store');
        Route::get('sales/{sale}', [AdminSaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/invoice', [AdminSaleController::class, 'downloadInvoice'])->name('sales.invoice');

        // Log Aktivitas
        Route::get('activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');

        // Notifikasi Stok Rendah
        Route::get('notifications', [AdminNotificationController::class, 'index'])
            ->name('notifications.index');
        Route::post('notifications/read-all', [AdminNotificationController::class, 'markAllRead'])
            ->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])
            ->name('notifications.read');
    });

/*
|--------------------------------------------------------------------------
| Route Kasir — hanya untuk pengguna dengan peran 'kasir'
|--------------------------------------------------------------------------
*/
Route::prefix('kasir')
    ->middleware(['auth', 'role:kasir'])
    ->name('kasir.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('kasir.dashboard');
        })->name('dashboard');

        // Penjualan
        Route::get('sales', [KasirSaleController::class, 'index'])->name('sales.index');
        Route::get('sales/create', [KasirSaleController::class, 'create'])->name('sales.create');
        Route::post('sales', [KasirSaleController::class, 'store'])->name('sales.store');
        Route::get('sales/{sale}', [KasirSaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/invoice', [KasirSaleController::class, 'downloadInvoice'])->name('sales.invoice');
    });

/*
|--------------------------------------------------------------------------
| Route Gudang — hanya untuk pengguna dengan peran 'gudang'
|--------------------------------------------------------------------------
*/
Route::prefix('gudang')
    ->middleware(['auth', 'role:gudang'])
    ->name('gudang.')
    ->group(function () {
        Route::get('/dashboard', function () {
            return view('gudang.dashboard');
        })->name('dashboard');

        // Manajemen Produk (view + edit only)
        Route::get('products', [GudangProductController::class, 'index'])->name('products.index');
        Route::get('products/{product}/edit', [GudangProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [GudangProductController::class, 'update'])->name('products.update');

        // Stok Masuk
        Route::resource('stock-ins', GudangStockInController::class)
            ->only(['index', 'create', 'store', 'show']);

        // Penyesuaian Stok
        Route::resource('stock-adjustments', GudangStockAdjustmentController::class)
            ->only(['index', 'create', 'store', 'show']);

        // Notifikasi Stok Rendah
        Route::get('notifications', [GudangNotificationController::class, 'index'])
            ->name('notifications.index');
        Route::post('notifications/read-all', [GudangNotificationController::class, 'markAllRead'])
            ->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [GudangNotificationController::class, 'markRead'])
            ->name('notifications.read');
    });

/*
|--------------------------------------------------------------------------
| Route Profil (bawaan Breeze — bisa dihapus setelah modul pengguna siap)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
