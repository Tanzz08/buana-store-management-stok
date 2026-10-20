<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

/**
 * Kebijakan otorisasi untuk modul Penjualan.
 *
 * Akses:
 * - Admin  : Hanya bisa melihat (tidak bisa membuat transaksi)
 * - Kasir  : Bisa membuat dan melihat transaksi miliknya
 * - Gudang : Tidak ada akses
 */
class SalePolicy
{
    /**
     * Admin dan Kasir bisa melihat daftar transaksi penjualan.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'kasir'], true);
    }

    /**
     * Admin bisa melihat semua transaksi.
     * Kasir hanya bisa melihat transaksi yang dibuatnya sendiri.
     */
    public function view(User $user, Sale $sale): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'kasir') {
            return $sale->user_id === $user->id;
        }

        return false;
    }

    /**
     * Hanya Kasir yang bisa membuat transaksi penjualan baru.
     */
    public function create(User $user): bool
    {
       return $user->role === 'kasir' || $user->role === 'admin';
    }

    /**
     * Tidak ada yang bisa mengubah transaksi yang sudah tersimpan.
     * (Penjualan bersifat final setelah disimpan)
     */
    public function update(User $user, Sale $sale): bool
    {
        return false;
    }

    /**
     * Tidak ada yang bisa menghapus transaksi penjualan.
     */
    public function delete(User $user, Sale $sale): bool
    {
        return false;
    }

    /**
     * Admin dan Kasir bisa mengunduh invoice PDF.
     * Kasir hanya bisa mengunduh invoice miliknya sendiri.
     */
    public function downloadInvoice(User $user, Sale $sale): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'kasir') {
            return $sale->user_id === $user->id;
        }

        return false;
    }
}
