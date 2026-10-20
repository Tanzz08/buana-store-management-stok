<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar ketika mencoba menghapus entitas yang masih memiliki relasi aktif.
 *
 * Contoh:
 *   throw new EntityHasDependenciesException('Kategori tidak dapat dihapus karena masih memiliki produk aktif.');
 *   throw new EntityHasDependenciesException('Pemasok tidak dapat dihapus karena memiliki riwayat transaksi.');
 */
class EntityHasDependenciesException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
