<?php

namespace Database\Seeders\Tenant;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Notifications\TenantWelcomeNotification;
use Illuminate\Support\Facades\Password;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = tenant()->admin_email;

        if ($adminEmail) {
            $user = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Administrador',
                    'password' => Hash::make(str()->random(16)),
                ]
            );

            // This will now work because it's running inside the tenant's context
            $user->assignRole('Admin Tenant');

            // Send welcome email
            $token = Password::broker()->createToken($user);
            $user->notify(new TenantWelcomeNotification($token));
        }
    }
}
