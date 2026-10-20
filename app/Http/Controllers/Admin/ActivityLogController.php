<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Tampilkan halaman riwayat aktivitas login/logout.
     * Hanya dapat diakses oleh pengguna dengan peran Admin.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $logs = ActivityLog::with('user')
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.activity-logs.index', compact('logs', 'search'));
    }
}
