<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Central\SuperAdminSeeder;
use Database\Seeders\Central\LeadSeeder; // Importe o LeadSeeder também

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
            LeadSeeder::class
        ]);
    }
}
