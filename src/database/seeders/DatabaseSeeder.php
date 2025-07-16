<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Central\SuperAdminSeeder; // Importe o novo seeder

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chama o seeder para criar o Super Admin no banco de dados central.
        $this->call([
            SuperAdminSeeder::class
        ]);
    }
}
