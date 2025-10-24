<?php

namespace App\Actions\Fortify;

use App\Models\Tenant\Bairro; // <-- ADICIONADO: Importar o model Bairro
use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Multitenancy\Models\Tenant;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Valida e cria um novo usuário registrado.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:tenant.users'],
            'password' => $this->passwordRules(),

            // --- VALIDAÇÃO LGPD ADICIONADA ---
            'terms' => ['accepted', 'required'],
            'privacy' => ['accepted', 'required'],

            // Validação dos dados do perfil
            'cpf' => ['nullable', 'string', 'max:14', 'unique:tenant.users,cpf'],
            'profile_data.telefone' => ['nullable', 'string', 'max:20'],
            'profile_data.data_nascimento' => ['nullable', 'date'],
            'profile_data.genero' => ['nullable', 'string', 'max:50'],
            'profile_data.nome_mae' => ['nullable', 'string', 'max:255'],
            'profile_data.nome_pai' => ['nullable', 'string', 'max:255'],
            'profile_data.endereco_cep' => ['nullable', 'string', 'max:9'],
            'profile_data.endereco_logradouro' => ['nullable', 'string', 'max:255'],
            'profile_data.endereco_numero' => ['nullable', 'string', 'max:20'],
            
            // ATUALIZADO: Validação do Bairro
            // Agora é 'required' (conforme lógica do form) e não mais 'integer' ou 'exists'.
            // Pode ser um ID (int) ou um nome de bairro (string).
            'bairro_id' => ['required'], 
            
            'profile_data.endereco_cidade' => ['required', 'string', 'max:100'],
            'profile_data.endereco_estado' => ['nullable', 'string', 'max:2'],

        ])->validate();

        // Lógica de verificação da cidade do cidadão
        $tenant = Tenant::current();
        if ($tenant && ! $tenant->permite_cadastro_cidade_externa) {
            $cidadeInput = $input['profile_data']['endereco_cidade'] ?? '';
            if (Str::lower($cidadeInput) !== Str::lower($tenant->endereco_cidade)) {
                // Lança uma exceção de validação que o Inertia entende
                throw ValidationException::withMessages([
                    'profile_data.endereco_cidade' => 'Não é permitido o cadastro de cidadãos de outras cidades.',
                ]);
            }
        }

        // MODIFICADO: Utiliza uma transação e a lógica de verificação do bairro_id.
        return DB::transaction(function () use ($input) {

            // --- INÍCIO: LÓGICA DE BAIRRO NOVO OU EXISTENTE ---
            $bairroId = $input['bairro_id'] ?? null;

            // Verifica se o valor recebido é um texto (string) em vez de um ID (numérico).
            if ($bairroId && !is_numeric($bairroId)) {
                // É um texto, então é um novo bairro sugerido.
                // Criamos o novo bairro com o nome recebido e o marcamos como 'não aprovado'.
                $novoBairro = Bairro::create([
                    'nome' => $bairroId,       // $bairroId aqui contém o texto, ex: "Vila da Paz"
                    'aprovado' => false,   // Nasce como pendente de aprovação
                ]);
                
                // Atualiza a variável $bairroId para o ID do registro recém-criado.
                $bairroId = $novoBairro->id;
            }
            // Se $bairroId já era numérico, ele passa direto e é usado.
            // --- FIM: LÓGICA DE BAIRRO ---

            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'cpf' => $input['cpf'] ?? null,
                'profile_data' => $input['profile_data'] ?? [],
                'bairro_id' => $bairroId, // Associa o ID do bairro (seja o existente ou o novo)
                // --- REGISTRO DO CONSENTIMENTO LGPD ---
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
            ]);

            // Atribui o papel de Cidadão ao novo usuário
            $role = Role::where('name', 'Cidadao')->where('guard_name', 'tenant')->first();
            if ($role) {
                $user->assignRole($role);
            }

            return $user;
        });
    }
}
