<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $users = User::when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        })
        ->orderBy('name')
        ->paginate(20)
        ->withQueryString();

        return view('admin.users.index', compact('users', 'search'));
    }

    /**
     * Tampilkan form tambah pengguna baru.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password, // di-hash otomatis via cast
            'role'     => $request->role,
            'is_active' => true,
        ]);

        // Kirim email verifikasi via Queue
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email verifikasi ke ' . $user->email . ': ' . $e->getMessage());
            return redirect()->route('admin.users.index')
                ->with('success', 'Pengguna berhasil dibuat.')
                ->with('warning', 'Email verifikasi gagal dikirim. Akun disimpan dengan status Belum Terverifikasi.');
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dibuat dan email verifikasi telah dikirimkan.');
    }

    /**
     * Tampilkan detail satu pengguna.
     */
    public function show(User $user): View
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Tampilkan form edit pengguna.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Perbarui data pengguna.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $roleChanged     = $request->role !== $user->role;
        $isActiveChanged = $request->has('is_active') && ((bool) $request->is_active) !== $user->is_active;

        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            'is_active' => $request->boolean('is_active', $user->is_active),
        ]);

        // Cabut seluruh sesi aktif jika role atau status aktif berubah
        if ($roleChanged || $isActiveChanged) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Nonaktifkan akun pengguna (soft-disable, bukan hard delete).
     */
    public function destroy(User $user): RedirectResponse
    {
        // Jangan hapus akun admin sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => false]);

        // Cabut sesi aktif pengguna tersebut
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun pengguna berhasil dinonaktifkan.');
    }
}
