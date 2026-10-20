<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar ketika stok produk tidak mencukupi saat pemrosesan penjualan.
 *
 * Contoh:
 *   throw new InsufficientStockException('iPhone 15', 3);
 *   // → "Stok iPhone 15 tidak mencukupi. Stok tersedia: 3 unit."
 */
class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly string $productName,
        public readonly int $availableStock,
    ) {
        parent::__construct(
            "Stok {$productName} tidak mencukupi. Stok tersedia: {$availableStock} unit."
        );
    }
}
