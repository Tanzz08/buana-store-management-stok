<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

/**
 * Kebijakan otorisasi untuk modul Pemasok.
 *
 * Akses: Admin saja (CRUD penuh).
 * Kasir dan Gudang tidak dapat mengelola data pemasok.
 */
class SupplierPolicy
{
    /**
     * Hanya Admin yang bisa melihat daftar pemasok.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa melihat detail pemasok.
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa membuat pemasok baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa memperbarui data pemasok.
     */
    public function update(User $user, Supplier $supplier): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa menghapus pemasok.
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->role === 'admin';
    }
}
