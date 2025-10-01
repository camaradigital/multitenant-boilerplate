<?php

namespace App\Actions\Fortify\Tenant;

use App\Actions\Fortify\PasswordValidationRules;
use Illuminate\Contracts\Auth\CanResetPassword; // Importante: Usa a interface para maior compatibilidade.
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(CanResetPassword $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();

        // LÓGICA ADICIONADA: Marca o e-mail como verificado automaticamente.
        if (method_exists($user, 'markEmailAsVerified') && ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
    }
}
