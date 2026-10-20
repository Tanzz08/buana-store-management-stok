<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ActivityLog;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user with the given data.
     */
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update the role of an existing user.
     */
    public function updateRole(int $userId, string $role): void
    {
        User::where('id', $userId)->update(['role' => $role]);
    }

    /**
     * Deactivate (soft-disable) a user account.
     */
    public function deactivate(int $userId): void
    {
        User::where('id', $userId)->update(['is_active' => false]);
    }

    /**
     * Append an entry to the `activity_logs` table.
     */
    public function logActivity(int $userId, string $action, string $ip): void
    {
        ActivityLog::create([
            'user_id'    => $userId,
            'action'     => $action,
            'ip_address' => $ip,
            'created_at' => now(),
        ]);
    }
}
