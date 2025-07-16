<?php

// ARQUIVO 4: app/Actions/Fortify/Tenant/ResetUserPassword.php
// Objetivo: Logar informações cruciais no momento exato da tentativa de reset.
namespace App\Actions\Fortify\Tenant;

use App\Models\Tenant\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Adicionado
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use App\Actions\Fortify\PasswordValidationRules;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        // --- LOGS DE DEBUG ---
        $currentConnection = $user->getConnectionName(); // Pega a conexão do próprio modelo
        Log::info("[DEBUG] Tenant\ResetUserPassword@reset: Ação de reset do TENANT iniciada.");
        Log::info("[DEBUG] Tenant\ResetUserPassword@reset: Conexão de DB usada pelo modelo User: '{$currentConnection}'.");
        Log::info("[DEBUG] Tenant\ResetUserPassword@reset: Tentando resetar a senha para o usuário ID: {$user->id} com email: {$user->email}.");
        // --- FIM DOS LOGS ---

        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        Log::info("[DEBUG] Tenant\ResetUserPassword@reset: Senha resetada com SUCESSO para o usuário ID: {$user->id}.");
    }
}
