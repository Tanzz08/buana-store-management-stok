<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Kebijakan otorisasi untuk modul Produk.
 *
 * Akses:
 * - Admin  : CRUD penuh, termasuk aktifkan/nonaktifkan dan impor CSV
 * - Gudang : Bisa melihat dan mengedit produk (tidak termasuk aktif/nonaktif dan impor CSV)
 * - Kasir  : Tidak ada akses ke manajemen produk (hanya bisa search via endpoint Kasir)
 */
class ProductPolicy
{
    /**
     * Admin dan Gudang bisa melihat daftar produk.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Admin dan Gudang bisa melihat detail produk.
     */
    public function view(User $user, Product $product): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Hanya Admin yang bisa membuat produk baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Admin dan Gudang bisa mengedit data produk.
     * (Gudang tidak bisa mengubah status aktif/nonaktif — diatur di updateStatus)
     */
    public function update(User $user, Product $product): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Hanya Admin yang bisa mengaktifkan atau menonaktifkan produk.
     */
    public function updateStatus(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa menghapus produk.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa mengimpor produk via CSV.
     */
    public function import(User $user): bool
    {
        return $user->role === 'admin';
    }
}
