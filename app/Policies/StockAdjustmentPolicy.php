<?php

namespace App\Policies;

use App\Models\StockAdjustment;
use App\Models\User;

/**
 * Kebijakan otorisasi untuk modul Penyesuaian Stok (Stok Opname).
 *
 * Akses:
 * - Admin  : CRUD penuh
 * - Gudang : CRUD penuh
 * - Kasir  : Tidak ada akses
 */
class StockAdjustmentPolicy
{
    /**
     * Admin dan Gudang bisa melihat daftar riwayat penyesuaian stok.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Admin dan Gudang bisa melihat detail penyesuaian stok.
     */
    public function view(User $user, StockAdjustment $stockAdjustment): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Admin dan Gudang bisa membuat penyesuaian stok baru.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Hanya Admin yang bisa mengubah catatan penyesuaian stok.
     */
    public function update(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa menghapus catatan penyesuaian stok.
     */
    public function delete(User $user, StockAdjustment $stockAdjustment): bool
    {
        return $user->role === 'admin';
    }
}
