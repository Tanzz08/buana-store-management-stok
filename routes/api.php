<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rute AJAX untuk endpoint internal (notifikasi badge polling, stok produk).
| Semua rute di sini dilindungi middleware 'auth' dan 'web'.
|
*/

Route::middleware(['auth', 'web'])->group(function () {
    // Jumlah notifikasi stok rendah yang belum dibaca — digunakan oleh badge polling
    Route::get('/notifications/count', function (\Illuminate\Http\Request $request) {
        $count = app(\App\Services\NotificationService::class)
            ->getUnreadCountForUser($request->user()->id);
        return response()->json(['count' => $count]);
    });

    // Stok terkini produk — digunakan oleh form penjualan untuk validasi sisi klien
    Route::get('/products/{product}/stock', function (\App\Models\Product $product) {
        return response()->json([
            'id'             => $product->id,
            'name'           => $product->name,
            'sku'            => $product->sku,
            'selling_price'  => (float) $product->selling_price,
            'current_stock'  => $product->current_stock,
            'unit'           => $product->unit,
            'status'         => $product->status,
        ]);
    });

    // Daftar produk aktif — digunakan oleh form penjualan (search)
    Route::get('/products/search', function (\Illuminate\Http\Request $request) {
        $keyword  = (string) $request->query('q', '');
        $products = \App\Models\Product::active()
            ->with('category')
            ->when($keyword !== '', function ($query) use ($keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'ILIKE', "%{$keyword}%")
                      ->orWhere('sku', 'ILIKE', "%{$keyword}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'sku', 'selling_price', 'current_stock', 'unit']);

        return response()->json($products);
    });
});
