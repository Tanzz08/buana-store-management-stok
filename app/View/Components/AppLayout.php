<?php

namespace App\View\Components;

use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;
use Throwable;

class AppLayout extends Component
{
    /**
     * Jumlah notifikasi stok rendah yang belum dibaca oleh pengguna yang sedang login.
     * Hanya dihitung untuk peran admin dan gudang yang memiliki akses notifikasi.
     */
    public int $notificationCount = 0;

    public function __construct()
    {
        try {
            $user = Auth::user();

            if ($user && in_array($user->role, ['admin', 'gudang'], true)) {
                /** @var NotificationService $service */
                $service = app(NotificationService::class);
                $this->notificationCount = $service->getUnreadCountForUser($user->id);
            }
        } catch (Throwable) {
            // Jika terjadi error (misal: saat testing tanpa database), abaikan
            $this->notificationCount = 0;
        }
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
