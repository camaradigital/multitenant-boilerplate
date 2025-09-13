<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class EncryptedCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return null;
        }

        try {
            // Descriptografa o valor do banco de dados.
            // O retorno é um JSON string, então ele é decodificado para um array.
            $decrypted = Crypt::decryptString($value);
            return json_decode($decrypted, true);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Se houver um erro na descriptografia, retorna um array vazio
            // para evitar quebras no sistema. É importante logar este erro.
            logger()->error('Falha ao descriptografar dados do perfil do usuário.', [
                'user_id' => $model->id,
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($value === null) {
            return null;
        }

        // Verifica se o valor é um array ou objeto, pois ele será convertido para JSON.
        if (!is_array($value) && !is_object($value)) {
            throw new InvalidArgumentException('O valor para o cast EncyptedCast deve ser um array ou objeto.');
        }

        // Codifica o array para uma string JSON e a criptografa.
        return Crypt::encryptString(json_encode($value));
    }
}
