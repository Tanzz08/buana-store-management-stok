<?php

namespace App\Policies;

use App\Models\LowStockNotification;
use App\Models\User;

/**
 * Kebijakan otorisasi untuk modul Notifikasi Stok Rendah.
 *
 * Akses:
 * - Admin  : Bisa melihat dan menandai notifikasi
 * - Gudang : Bisa melihat dan menandai notifikasi
 * - Kasir  : Tidak ada akses ke notifikasi stok
 */
class NotificationPolicy
{
    /**
     * Admin dan Gudang bisa melihat daftar notifikasi stok rendah.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Admin dan Gudang bisa melihat detail notifikasi.
     */
    public function view(User $user, LowStockNotification $notification): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Admin dan Gudang bisa menandai notifikasi sebagai sudah dibaca.
     */
    public function markAsRead(User $user, LowStockNotification $notification): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }

    /**
     * Admin dan Gudang bisa menandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllAsRead(User $user): bool
    {
        return in_array($user->role, ['admin', 'gudang'], true);
    }
}
