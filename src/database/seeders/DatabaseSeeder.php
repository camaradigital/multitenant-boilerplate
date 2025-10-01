<?php

namespace Database\Seeders;

use Database\Seeders\Central\LeadSeeder;
use Database\Seeders\Central\SuperAdminSeeder;
use Illuminate\Database\Seeder; // Importe o LeadSeeder também

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chama os seeders para popular o banco de dados.
        $this->call([
            SuperAdminSeeder::class,
            LeadSeeder::class,
        ]);
    }
}
