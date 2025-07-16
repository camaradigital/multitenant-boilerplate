<?php

namespace App\Auth\Passwords;

use Illuminate\Auth\Passwords\DatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Log;

class CustomDatabaseTokenRepository extends DatabaseTokenRepository
{
    /**
     * Create a new token record.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return string
     */
    public function create(CanResetPasswordContract $user)
    {
        $email = $user->getEmailForPasswordReset();
        $this->deleteExisting($user);

        // We will create a new token for the user so that we can encrypt it and store
        // it in the database. The token is enclosed in a check task to determine
        // if the user's token has expired since the last time it was created.
        $token = $this->createNewToken();

        $this->getTable()->insert($this->getPayload($email, $token));

        // LOG ADICIONADO
        $hashedToken = $this->hasher->make($token);
        Log::info("[DEBUG] CustomDatabaseTokenRepository@create: Token CRIADO para email '{$email}'. Hash: '{$hashedToken}'.");

        return $token;
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $token
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, $token)
    {
        $email = $user->getEmailForPasswordReset();
        Log::info("[DEBUG] CustomDatabaseTokenRepository@exists: Token VERIFICADO para email '{$email}'.");

        $record = (array) $this->getTable()->where(
            'email', $email
        )->first();

        if ($record) {
            Log::info("[DEBUG] CustomDatabaseTokenRepository@exists: Registro encontrado para '{$email}'. Hash no DB: '{$record['token']}'.");
            $hashesMatch = $this->hasher->check($token, $record['token']);
            Log::info("[DEBUG] CustomDatabaseTokenRepository@exists: Hashes correspondem? " . ($hashesMatch ? 'SIM' : 'NÃO'));
            return $hashesMatch &&
                   ! $this->tokenExpired($record['created_at']);
        }

        Log::info("[DEBUG] CustomDatabaseTokenRepository@exists: Nenhum registro de token encontrado para '{$email}'.");
        return false;
    }
}
