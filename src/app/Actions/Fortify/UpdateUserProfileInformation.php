<?php

namespace App\Actions\Fortify;

use App\Models\Tenant\CustomField;
use App\Models\Tenant\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        // Pega os nomes dos campos personalizados do banco de dados.
        $customFieldNames = CustomField::pluck('name')->toArray();

        // Define a lista COMPLETA de campos padrão que pertencem ao profile_data.
        $standardProfileFields = [
            'telefone',
            'endereco_cep',
            'endereco_logradouro',
            'endereco_numero',
            'endereco_bairro',
            'endereco_cidade',
            'endereco_estado',
            // --- CAMPOS ADICIONADOS PARA CORREÇÃO ---
            'data_nascimento',
            'genero',
            'nome_mae',
            'nome_pai',
        ];

        // Junta os campos padrão e os personalizados em uma única lista.
        $allProfileDataKeys = array_merge($standardProfileFields, $customFieldNames);

        // Valida os dados de entrada.
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],

            // Validações para os campos de profile_data
            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco_cep' => ['nullable', 'string', 'max:10'],
            'data_nascimento' => ['nullable', 'date'],
            'genero' => ['nullable', 'string', 'max:50'],
            'nome_mae' => ['nullable', 'string', 'max:255'],
            'nome_pai' => ['nullable', 'string', 'max:255'],

        ])->validateWithBag('updateProfileInformation');

        // Atualiza a foto de perfil, se houver.
        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        // Atualiza nome, e-mail e o status de verificação.
        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }

        // Agrupa e salva todos os dados de 'profile_data'.
        $profileData = collect($input)->only($allProfileDataKeys)->toArray();
        $user->profile_data = $profileData;
        $user->save();
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
