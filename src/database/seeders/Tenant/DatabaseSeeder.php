<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            RoleSeeder::class,      // Primeiro cria o papel
            AdminUserSeeder::class, // Depois cria o usuário e atribui o papel
        ]);
    }
}
