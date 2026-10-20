<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\LowStockNotification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Tampilkan daftar notifikasi stok rendah untuk Gudang.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LowStockNotification::class);

        $userId        = $request->user()->id;
        $notifications = $this->notificationService->getForUser($userId);
        $unreadCount   = $this->notificationService->getUnreadCountForUser($userId);

        return view('gudang.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markRead(Request $request, LowStockNotification $notification): RedirectResponse
    {
        $this->authorize('markAsRead', $notification);

        $this->notificationService->markAsRead($notification->id, $request->user()->id);

        return redirect()
            ->route('gudang.notifications.index')
            ->with('success', 'Notifikasi telah ditandai sebagai sudah dibaca.');
    }

    /**
     * Tandai semua notifikasi sebagai sudah dibaca.
     */
    public function markAllRead(Request $request): RedirectResponse
    {
        $this->authorize('markAllAsRead', LowStockNotification::class);

        $this->notificationService->markAllAsRead($request->user()->id);

        return redirect()
            ->route('gudang.notifications.index')
            ->with('success', 'Semua notifikasi telah ditandai sebagai sudah dibaca.');
    }
}
