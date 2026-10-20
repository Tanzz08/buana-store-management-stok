<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::factory()->admin()->create([
            'name'  => 'Admin Buana Store',
            'email' => 'admin@buanastore.com',
        ]);

        // Kasir
        User::factory()->kasir()->create([
            'name'  => 'Kasir Buana Store',
            'email' => 'kasir@buanastore.com',
        ]);

        // Gudang
        User::factory()->gudang()->create([
            'name'  => 'Gudang Buana Store',
            'email' => 'gudang@buanastore.com',
        ]);
    }
}
