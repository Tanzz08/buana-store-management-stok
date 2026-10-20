<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\EntityHasDependenciesException;
use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Service layer untuk modul Manajemen Pemasok.
 *
 * Mengelola logika bisnis untuk CRUD pemasok, termasuk
 * validasi keunikan nama dan perlindungan penghapusan
 * pemasok yang masih memiliki riwayat stok masuk.
 */
class SupplierService
{
    public function __construct(
        private readonly SupplierRepositoryInterface $supplierRepository,
    ) {}

    /**
     * Ambil semua pemasok terurut berdasarkan nama.
     *
     * @return Collection<int, Supplier>
     */
    public function all(): Collection
    {
        return $this->supplierRepository->all();
    }

    /**
     * Buat pemasok baru setelah memvalidasi keunikan nama.
     *
     * @param  array{name: string, phone?: string|null, email?: string|null, address?: string|null} $data
     * @throws ValidationException jika nama sudah digunakan
     */
    public function create(array $data): Supplier
    {
        if ($this->supplierRepository->findByName($data['name']) !== null) {
            throw ValidationException::withMessages([
                'name' => ['Nama pemasok sudah terdaftar.'],
            ]);
        }

        return $this->supplierRepository->create([
            'name'    => $data['name'],
            'phone'   => $data['phone'] ?? null,
            'email'   => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
        ]);
    }

    /**
     * Perbarui data pemasok setelah memvalidasi keunikan nama
     * (mengecualikan pemasok yang sedang diubah).
     *
     * @param  array{name: string, phone?: string|null, email?: string|null, address?: string|null} $data
     * @throws ValidationException jika nama sudah digunakan oleh pemasok lain
     */
    public function update(int $id, array $data): Supplier
    {
        if ($this->supplierRepository->findByName($data['name'], $id) !== null) {
            throw ValidationException::withMessages([
                'name' => ['Nama pemasok sudah terdaftar.'],
            ]);
        }

        return $this->supplierRepository->update($id, [
            'name'    => $data['name'],
            'phone'   => $data['phone'] ?? null,
            'email'   => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
        ]);
    }

    /**
     * Hapus pemasok jika tidak memiliki riwayat stok masuk.
     *
     * @throws EntityHasDependenciesException jika pemasok masih memiliki riwayat transaksi
     */
    public function delete(int $id): void
    {
        if ($this->supplierRepository->hasStockIns($id)) {
            throw new EntityHasDependenciesException(
                'Pemasok tidak dapat dihapus karena memiliki riwayat transaksi.'
            );
        }

        $this->supplierRepository->delete($id);
    }
}
