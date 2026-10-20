<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Kebijakan otorisasi untuk modul Kategori.
 *
 * Akses: Admin saja (CRUD penuh).
 * Kasir dan Gudang tidak dapat mengelola kategori.
 */
class CategoryPolicy
{
    /**
     * Hanya Admin yang bisa melihat daftar kategori.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa melihat detail satu kategori.
     */
    public function view(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa membuat kategori baru.
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa memperbarui kategori.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Hanya Admin yang bisa menghapus kategori.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->role === 'admin';
    }
}
