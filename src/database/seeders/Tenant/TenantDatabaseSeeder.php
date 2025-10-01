<?php

// Caminho: database/seeders/Tenant/TenantDatabaseSeeder.php

namespace Database\Seeders\Tenant;

use App\Models\Central\Tenant;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(Tenant $tenant): void
    {
        // Estes seeders não precisam do objeto $tenant, apenas do contexto
        // de banco de dados correto, que já foi definido pelo ->execute().
        $this->call([
            PermissionsSeeder::class,
            RolePermissionSeeder::class,
        ]);

        // Este seeder PRECISA do objeto $tenant para pegar o e-mail do admin.
        // Por isso, usamos callWith() para ele.
        $this->callWith(AdminUserSeeder::class, ['tenant' => $tenant]);
    }
}
