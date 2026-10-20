<?php

namespace App\Policies;

use App\Models\User;

/**
 * Kebijakan otorisasi untuk modul Laporan dan Analitik.
 *
 * Akses: Admin saja.
 * Kasir dan Gudang tidak dapat mengakses laporan.
 */
class ReportPolicy
{
    /**
     * Hanya Admin yang bisa melihat laporan nilai inventaris.
     */
    public function viewInventory(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa melihat laporan penjualan.
     */
    public function viewSales(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa melihat laporan mutasi stok.
     */
    public function viewStockMutation(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa mengunduh laporan dalam format Excel.
     */
    public function export(User $user): bool
    {
        return $user->role === 'admin';
    }
}
