<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\LowStockNotification;
use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{
    /**
     * Create a new low-stock notification for a product only if no active
     * notification (status: 'unread' or 'read') already exists for it.
     *
     * @param  int $productId     FK to `products.id`.
     * @param  int $currentStock  The stock level at the time the notification is triggered.
     */
    public function createIfNotExists(int $productId, int $currentStock): void;

    /**
     * Resolve all active notifications for a product by setting their status
     * to 'resolved' and stamping `resolved_at` with the current timestamp.
     *
     * Called when incoming stock brings `current_stock` above `minimum_stock`.
     */
    public function resolveIfExists(int $productId): void;

    /**
     * Count how many notifications with status 'unread' exist for a given user.
     *
     * The count is derived from `low_stock_notifications` that have NOT yet
     * been recorded in `notification_reads` for the specified user.
     */
    public function getUnreadCountForUser(int $userId): int;

    /**
     * Mark a single notification as read for the given user.
     *
     * Creates a record in `notification_reads`. If every relevant user has
     * now read the notification, the notification's own status may be
     * transitioned to 'read'.
     */
    public function markAsRead(int $notificationId, int $userId): void;

    /**
     * Mark all currently unread notifications as read for the given user.
     *
     * Performs a batch insert into `notification_reads` for all unread
     * notifications not yet read by this user.
     */
    public function markAllAsRead(int $userId): void;

    /**
     * Retrieve all notifications visible to the given user, ordered by
     * creation date descending.
     *
     * Implementations may join `notification_reads` to annotate each item
     * with a per-user read status.
     *
     * @return Collection<int, LowStockNotification>
     */
    public function getForUser(int $userId): Collection;
}
