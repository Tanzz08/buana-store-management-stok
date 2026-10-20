<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Find a user by their email address.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user with the given data.
     */
    public function createUser(array $data): User;

    /**
     * Update the role of an existing user.
     *
     * @param  int    $userId  The user's primary key.
     * @param  string $role    One of: 'admin', 'kasir', 'gudang'.
     */
    public function updateRole(int $userId, string $role): void;

    /**
     * Deactivate (soft-disable) a user account.
     *
     * Sets `is_active = false` without deleting the record.
     */
    public function deactivate(int $userId): void;

    /**
     * Append an entry to the `activity_logs` table.
     *
     * @param  int    $userId  The authenticated user performing the action.
     * @param  string $action  A short descriptor, e.g. 'login' or 'logout'.
     * @param  string $ip      The client IP address (IPv4 or IPv6).
     */
    public function logActivity(int $userId, string $action, string $ip): void;
}
