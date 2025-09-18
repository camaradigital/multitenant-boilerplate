<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\CustomField;
use App\Models\Tenant\User;
use App\Notifications\Tenant\SetInitialPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Spatie\Multitenancy\Models\Tenant;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class CidadaoController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAnyCidadao', User::class);

        return inertia('Tenant/Cidadaos/Index', [
            'cidadaos' => User::role('Cidadao')->latest()->paginate(10),
            'customFields' => CustomField::all(),
            'cidadeTenant' => Tenant::current()->endereco_cidade,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('createCidadao', User::class);

        $customRules = $this->getCustomFieldRules();

        $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenant.users,email',
            'cpf' => 'nullable|string|max:14|unique:tenant.users,cpf',
            'profile_data.telefone' => 'nullable|string|max:20',
            'profile_data.data_nascimento' => 'nullable|date',
            'profile_data.genero' => 'nullable|string|max:50',
            'profile_data.nome_mae' => 'nullable|string|max:255',
            'profile_data.nome_pai' => 'nullable|string|max:255',
            'profile_data.endereco_cep' => 'nullable|string|max:9',
            'profile_data.endereco_logradouro' => 'nullable|string|max:255',
            'profile_data.endereco_numero' => 'nullable|string|max:20',
            'profile_data.endereco_bairro' => 'nullable|string|max:100',
            'profile_data.endereco_cidade' => 'required|string|max:100',
            'profile_data.endereco_estado' => 'nullable|string|max:2',
        ], $customRules));

        $tenant = Tenant::current();
        if (!$tenant->permite_cadastro_cidade_externa) {
            if (Str::lower($request->input('profile_data.endereco_cidade')) !== Str::lower($tenant->endereco_cidade)) {
                return Redirect::back()->with('error', 'Não é permitido cadastrar cidadãos de outras cidades.')->withInput();
            }
        }

        $randomPassword = Str::random(16);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'cpf' => $request->cpf,
            'password' => Hash::make($randomPassword),
            'profile_data' => $request->profile_data,
        ]);

        $user->assignRole('Cidadao');

        $token = Password::broker('tenant_users')->createToken($user);
        $user->notify(new SetInitialPassword($token));

        return Redirect::route('admin.cidadaos.index')->with('success', 'Cidadão criado com sucesso. Um e-mail para definição de senha foi enviado.');
    }

    public function show(User $cidadao)
    {
        $this->authorize('viewCidadao', $cidadao);

        $cidadao->load(['solicitacoes' => function ($query) {
            $query->with(['servico', 'status', 'atendente'])->latest();
        }]);

        return Inertia::render('Tenant/Cidadaos/Show', [
            'cidadao' => $cidadao,
        ]);
    }

    public function update(Request $request, User $cidadao)
    {
        $this->authorize('updateCidadao', $cidadao);

        $customRules = $this->getCustomFieldRules();

        $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenant.users,email,' . $cidadao->id,
            'cpf' => 'nullable|string|max:14|unique:tenant.users,cpf,' . $cidadao->id,
            'profile_data.telefone' => 'nullable|string|max:20',
            'profile_data.data_nascimento' => 'nullable|date',
            'profile_data.genero' => 'nullable|string|max:50',
            'profile_data.nome_mae' => 'nullable|string|max:255',
            'profile_data.nome_pai' => 'nullable|string|max:255',
            'profile_data.endereco_cep' => 'nullable|string|max:9',
            'profile_data.endereco_logradouro' => 'nullable|string|max:255',
            'profile_data.endereco_numero' => 'nullable|string|max:20',
            'profile_data.endereco_bairro' => 'nullable|string|max:100',
            'profile_data.endereco_cidade' => 'required|string|max:100',
            'profile_data.endereco_estado' => 'nullable|string|max:2',
        ], $customRules));

        $data = $request->only('name', 'email', 'cpf', 'profile_data');

        $cidadao->update($data);

        return Redirect::route('admin.cidadaos.index')->with('success', 'Cidadão atualizado com sucesso.');
    }

    public function destroy(User $cidadao)
    {
        $this->authorize('deleteCidadao', $cidadao);

        if ($cidadao->solicitacoes()->exists()) {
            return Redirect::back()->with('error', 'Não é possível excluir. Este cidadão já possui solicitações de serviço.');
        }
        $cidadao->delete();
        return Redirect::route('admin.cidadaos.index')->with('success', 'Cidadão excluído com sucesso.');
    }

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
                case 'number': $rule[] = 'numeric'; break;
                case 'date': $rule[] = 'date'; break;
                default: $rule[] = 'string'; $rule[] = 'max:255'; break;
            }
            $rules['profile_data.' . $field->name] = $rule;
        }
        return $rules;
    }

    /**
     * Anonimiza os dados de um cidadão para conformidade com a LGPD.
     */
    public function anonymize(User $cidadao): RedirectResponse
    {
        $this->authorize('anonymizeCidadao', $cidadao);

        $cidadao->update([
            'name' => 'Usuário Anônimo #' . $cidadao->id,
            'email' => 'anonymized_' . $cidadao->id . '@' . request()->getHost(),
            'cpf' => null,
            'profile_data' => null,
            'is_active' => false,
        ]);

        $cidadao->syncRoles([]);

        return redirect()->route('admin.cidadaos.index')->with('success', 'Dados do cidadão anonimizados com sucesso.');
    }

    /**
     * Exporta todos os dados de um cidadão em formato JSON (Portabilidade LGPD).
     */
    public function exportData(User $cidadao): JsonResponse
    {
        $this->authorize('exportDataCidadao', $cidadao);

        $cidadao->load([
            'solicitacoes.servico',
            'solicitacoes.status',
            'solicitacoes.atendente:id,name',
            'solicitacoes.pesquisa_satisfacao',
        ]);

        $fileName = 'dados_cidadao_' . $cidadao->id . '_' . now()->format('Y-m-d') . '.json';

        return response()->json($cidadao, 200, [
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Type' => 'application/json',
        ]);
    }
}

