<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Service layer untuk manajemen notifikasi stok rendah.
 *
 * Menyediakan operasi bisnis notifikasi: pembuatan, penyelesaian, pembacaan,
 * dan penghitungan badge untuk ditampilkan di navbar.
 */
class NotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notificationRepository,
    ) {}

    /**
     * Buat notifikasi stok rendah jika belum ada notifikasi aktif untuk produk tersebut.
     *
     * Notifikasi aktif = status 'unread' atau 'read'.
     * Duplikasi notifikasi untuk produk yang sama dicegah.
     */
    public function createIfNotExists(int $productId, int $currentStock): void
    {
        $this->notificationRepository->createIfNotExists($productId, $currentStock);
    }

    /**
     * Tandai semua notifikasi aktif produk sebagai 'resolved' ketika stok kembali normal.
     *
     * Dipanggil saat stok masuk membuat current_stock > minimum_stock.
     */
    public function resolveIfExists(int $productId): void
    {
        $this->notificationRepository->resolveIfExists($productId);
    }

    /**
     * Hitung jumlah notifikasi yang belum dibaca oleh pengguna tertentu.
     *
     * Notifikasi dianggap belum dibaca jika statusnya 'unread' dan belum ada
     * record di tabel notification_reads untuk pengguna tersebut.
     */
    public function getUnreadCountForUser(int $userId): int
    {
        return $this->notificationRepository->getUnreadCountForUser($userId);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca oleh pengguna.
     *
     * Membuat record di notification_reads dan mengubah status notifikasi
     * dari 'unread' ke 'read'.
     */
    public function markAsRead(int $notificationId, int $userId): void
    {
        $this->notificationRepository->markAsRead($notificationId, $userId);
    }

    /**
     * Tandai semua notifikasi yang belum dibaca sebagai sudah dibaca oleh pengguna.
     *
     * Batch insert ke notification_reads untuk semua notifikasi unread,
     * lalu update status menjadi 'read'.
     */
    public function markAllAsRead(int $userId): void
    {
        $this->notificationRepository->markAllAsRead($userId);
    }

    /**
     * Ambil semua notifikasi untuk ditampilkan ke pengguna, diurutkan dari terbaru.
     *
     * @return Collection<int, \App\Models\LowStockNotification>
     */
    public function getForUser(int $userId): Collection
    {
        return $this->notificationRepository->getForUser($userId);
    }
}
