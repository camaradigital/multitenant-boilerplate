<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Tenant\User;
use App\Models\Tenant\SolicitacaoServico; // Importe o modelo de solicitações

class ProfileController extends Controller
{

    public function show()
    {
        $user = Auth::user();
        $profileData = [];

        if ($user && $user->profile_data) {
            try {
                $profileData = json_decode(decrypt($user->profile_data), true);
            } catch (DecryptException $e) {
                // Loga o erro e define os dados como vazios para evitar falhas no front-end
                \Log::error("Erro ao descriptografar profile_data para o usuário {$user->id}: " . $e->getMessage());
                $profileData = [];
            }
        }

        return Inertia::render('Profile/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf' => $user->cpf,
                'profile_data' => $profileData,
            ],
            'confirmsTwoFactorAuthentication' => method_exists($user, 'twoFactorAuthEnabled')
                ? $user->twoFactorAuthEnabled()
                : false,
            'sessions' => collect(
                Auth::user()->sessions()->all()
            )->map(function ($session) {
                return (object) $session;
            }),
        ]);
    }

    /**
     * Exporta todos os dados do cidadão autenticado em formato JSON.
     */
    public function exportData(Request $request): JsonResponse
    {
        $user = $request->user();

        // Carrega o usuário com todo o seu histórico relacionado
        $user->load([
            'solicitacoes.servico',
            'solicitacoes.status',
            'solicitacoes.atendente:id,name',
            'solicitacoes.pesquisa_satisfacao',
        ]);

        $fileName = 'meus_dados_' . now()->format('Y-m-d') . '.json';

        // Retorna uma resposta JSON que força o download no navegador
        return response()->json($user, 200, [
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Anonimiza os dados do cidadão autenticado.
     */
    public function anonymizeAccount(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Medida de segurança: não permitir que funcionários se anonimizem por esta rota.
        if (!$user->hasRole('Cidadao')) {
            return Redirect::back()->with('error', 'Esta funcionalidade está disponível apenas para cidadãos.');
        }

        $user->update([
            'name' => 'Usuário Anônimo #' . $user->id,
            'email' => 'anonymized_' . $user->id . '@' . request()->getHost(),
            'cpf' => null,
            'profile_data' => null,
            'is_active' => false,
        ]);

        // Remove os papéis para desassociar permissões
        $user->syncRoles([]);

        // Desloga o usuário
        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Sua conta foi anonimizada com sucesso.');
    }

    /**
     * Deleta a conta do usuário autenticado.
     * Esta ação requer uma senha.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Certifica-se de que o usuário está autenticado
        $user = Auth::user();

        // Validação da senha para confirmar a exclusão
        if (!Hash::check($request->password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'password' => __('This password does not match our records.'),
            ]);
        }

        // --- LÓGICA ADICIONADA: VERIFICAR HISTÓRICO DE ATIVIDADES ---
        $hasSolicitacoes = SolicitacaoServico::where('user_id', $user->id)->exists();

        if ($hasSolicitacoes) {
            // Se houver registros, a exclusão total é negada.
            // O sistema deve sugerir a anonimização.
            return Redirect::back()->with('error', 'Sua conta não pode ser deletada totalmente devido a registros de atividades para fins de auditoria interna. Considere a opção de anonimizar sua conta, que preserva sua privacidade.');
        }

        // Se não houver registros, a exclusão total é permitida.
        $user->delete();

        // Faz o logout do usuário após a exclusão
        Auth::guard('tenant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
