<?php

namespace App\Policies;

use App\Models\StockIn;
use App\Models\User;

/**
 * Kebijakan otorisasi untuk modul Stok Masuk.
 *
 * Akses:
 * - Admin  : CRUD penuh
 * - Gudang : CRUD penuh
 * - Kasir  : Tidak ada akses
 */
class StockInPolicy
{
    /**
     * Admin dan Gudang bisa melihat daftar riwayat stok masuk.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Admin dan Gudang bisa melihat detail stok masuk.
     */
    public function view(User $user, StockIn $stockIn): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Admin dan Gudang bisa mencatat stok masuk baru.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Admin dan Gudang bisa memperbarui stok masuk.
     */
    public function update(User $user, StockIn $stockIn): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Hanya Admin yang bisa menghapus catatan stok masuk.
     */
    public function delete(User $user, StockIn $stockIn): bool
    {
        return $user->role === 'admin';
    }
}
