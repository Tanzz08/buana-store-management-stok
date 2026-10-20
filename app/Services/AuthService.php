<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Catat aktivitas login atau logout pengguna ke tabel activity_logs.
     *
     * @param int    $userId ID pengguna yang melakukan aksi.
     * @param string $action Jenis aksi: 'login' atau 'logout'.
     * @param string $ip     Alamat IP klien (IPv4 atau IPv6).
     */
    public function logActivity(int $userId, string $action, string $ip): void
    {
        $this->userRepository->logActivity($userId, $action, $ip);
    }
}
