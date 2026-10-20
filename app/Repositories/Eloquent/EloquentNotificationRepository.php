<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\LowStockNotification;
use App\Models\NotificationRead;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    /**
     * Create a new low-stock notification only if no active notification exists for the product.
     */
    public function createIfNotExists(int $productId, int $currentStock): void
    {
        $exists = LowStockNotification::where('product_id', $productId)
            ->active()
            ->exists();

        if (!$exists) {
            LowStockNotification::create([
                'product_id'       => $productId,
                'stock_at_creation' => $currentStock,
                'status'           => 'unread',
                'created_at'       => now(),
            ]);
        }
    }

    /**
     * Resolve all active notifications for a product by setting status to 'resolved'.
     */
    public function resolveIfExists(int $productId): void
    {
        LowStockNotification::where('product_id', $productId)
            ->active()
            ->update([
                'status'      => 'resolved',
                'resolved_at' => now(),
            ]);
    }

    /**
     * Count how many notifications with status 'unread' exist for a given user
     * (i.e., not yet recorded in notification_reads for this user).
     */
    public function getUnreadCountForUser(int $userId): int
    {
        return LowStockNotification::where('status', 'unread')
            ->whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('notification_reads')
                    ->whereColumn('notification_reads.notification_id', 'low_stock_notifications.id')
                    ->where('notification_reads.user_id', $userId);
            })
            ->count();
    }

    /**
     * Mark a single notification as read for the given user.
     */
    public function markAsRead(int $notificationId, int $userId): void
    {
        // Insert into notification_reads if not already present
        $alreadyRead = NotificationRead::where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->exists();

        if (!$alreadyRead) {
            NotificationRead::create([
                'notification_id' => $notificationId,
                'user_id'         => $userId,
                'read_at'         => now(),
            ]);
        }

        // Update the notification status to 'read' if it is still 'unread'
        LowStockNotification::where('id', $notificationId)
            ->where('status', 'unread')
            ->update(['status' => 'read']);
    }

    /**
     * Mark all currently unread notifications as read for the given user.
     */
    public function markAllAsRead(int $userId): void
    {
        $now = now();

        // Fetch all unread notifications not yet read by this user
        $unreadIds = LowStockNotification::where('status', 'unread')
            ->whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('notification_reads')
                    ->whereColumn('notification_reads.notification_id', 'low_stock_notifications.id')
                    ->where('notification_reads.user_id', $userId);
            })
            ->pluck('id');

        if ($unreadIds->isEmpty()) {
            return;
        }

        // Batch insert into notification_reads
        $inserts = $unreadIds->map(fn (int $id) => [
            'notification_id' => $id,
            'user_id'         => $userId,
            'read_at'         => $now,
        ])->all();

        NotificationRead::insert($inserts);

        // Update all those notifications to 'read'
        LowStockNotification::whereIn('id', $unreadIds)
            ->where('status', 'unread')
            ->update(['status' => 'read']);
    }

    /**
     * Retrieve all notifications visible to the given user, ordered by created_at desc.
     */
    public function getForUser(int $userId): Collection
    {
        return LowStockNotification::with(['product', 'reads' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }])
            ->orderByDesc('created_at')
            ->get();
    }
}
