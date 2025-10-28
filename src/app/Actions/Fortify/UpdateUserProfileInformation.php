<?php

namespace App\Actions\Fortify;

use App\Models\Tenant\CustomField;
use App\Models\Tenant\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        // Pega os nomes dos campos personalizados para a mesclagem.
        $customFieldNames = CustomField::pluck('name')->toArray();
        $standardProfileFields = [
            'telefone', 'endereco_cep', 'endereco_logradouro', 'endereco_numero',
            'endereco_cidade', 'endereco_estado', 'data_nascimento', 'genero',
            'nome_mae', 'nome_pai',
        ];
        $allProfileDataKeys = array_merge($standardProfileFields, $customFieldNames);

        // --- CORREÇÃO #2: Adiciona validação dinâmica ---
        $customRules = $this->getCustomFieldRules();

        // Valida os dados de entrada.
        Validator::make($input, array_merge([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],

            // --- CORREÇÃO #1: Bairro é obrigatório ---
            'bairro_id' => ['required', 'integer', 'exists:tenant.bairros,id'],

            // Validações para os campos padrão de profile_data
            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco_cep' => ['nullable', 'string', 'max:10'],
            'data_nascimento' => ['nullable', 'date'],
            'genero' => ['nullable', 'string', 'max:50'],
            'nome_mae' => ['nullable', 'string', 'max:255'],
            'nome_pai' => ['nullable', 'string', 'max:255'],
        
        ], $customRules))->validateWithBag('updateProfileInformation'); // <-- Mescla as regras

        // Atualiza a foto de perfil, se houver.
        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        // --- CORREÇÃO #3: Simplifica a Transação ---
        DB::transaction(function () use ($user, $input, $allProfileDataKeys) {
            
            // 1. Agrupa e mescla os dados de 'profile_data'.
            $currentProfileData = $user->profile_data ?? [];
            $newProfileData = collect($input)->only($allProfileDataKeys)->toArray();
            $mergedProfileData = array_merge($currentProfileData, $newProfileData);

            // 2. Prepara TODOS os dados para o update
            $dataToUpdate = [
                'name' => $input['name'],
                'email' => $input['email'],
                'profile_data' => $mergedProfileData,
                'bairro_id' => $input['bairro_id'], // Já validado como 'required'
            ];

            // 3. Verifica se o e-mail foi alterado e precisa de reverificação
            if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
                $dataToUpdate['email_verified_at'] = null;
                
                // Salva tudo de uma vez
                $user->forceFill($dataToUpdate)->save();
                
                // Envia a notificação
                $user->sendEmailVerificationNotification();
            } else {
                // Salva tudo de uma vez
                $user->forceFill($dataToUpdate)->save();
            }
        });
    }

    /**
     * Update the given verified user's profile information.
     * (Este método é chamado pelo 'if' acima e já está correto)
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save(); // Este save é interno ao método, mas a lógica acima foi ajustada

        $user->sendEmailVerificationNotification();
    }

    /**
     * Busca as regras de validação dos campos personalizados.
     * (Lógica extraída do seu CidadaoController)
     */
    protected function getCustomFieldRules(): array
    {
        $rules = [];
        $customFields = CustomField::all();
        foreach ($customFields as $field) {
            $rule = ['nullable'];
            if ($field->is_required) {
                $rule = ['required'];
            }
            switch ($field->type) {
                case 'number': $rule[] = 'numeric';
                    break;
                case 'date': $rule[] = 'date';
                    break;
                default: $rule[] = 'string';
                    $rule[] = 'max:255';
                    break;
            }
            // A validação é aplicada no nome do campo dentro de 'profile_data'
            $rules['profile_data.'.$field->name] = $rule;
        }

        return $rules;
    }
}
