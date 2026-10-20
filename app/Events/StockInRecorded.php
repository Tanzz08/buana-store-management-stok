<?php

namespace App\Events;

use App\Models\StockIn;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event yang di-fire setelah catatan stok masuk berhasil disimpan
 * dan stok produk telah diperbarui secara atomik.
 *
 * Listener dapat menggunakan event ini untuk tugas downstream seperti
 * pembuatan notifikasi atau audit lebih lanjut.
 */
class StockInRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly StockIn $stockIn,
    ) {}
}
