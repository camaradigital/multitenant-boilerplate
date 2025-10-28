<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Bairro;
use App\Models\Tenant\CustomField;
use App\Models\Tenant\SolicitacaoServico;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $this->authorize('viewProfile', $user);

        $profileData = [];

        if ($user && $user->profile_data) {
            try {
                $profileData = json_decode(decrypt($user->profile_data), true) ?? [];
            } catch (DecryptException $e) {
                Log::error("Erro ao descriptografar profile_data para o usuário {$user->id}: ".$e->getMessage());
                $profileData = [];
            }
        }

        return Inertia::render('Profile/Show', [
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'cpf' => $user->cpf ?? null,
                    'bairro_id' => $user->bairro_id ?? null,
                    'profile_photo_url' => $user->profile_photo_url ?? null,
                    'profile_data' => $profileData,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ],
            ],
            'confirmsTwoFactorAuthentication' => Features::enabled(Features::twoFactorAuthentication()) &&
                Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'sessions' => $this->sessions($user),
            'customFields' => CustomField::orderBy('label')->get(['id', 'name', 'label', 'type']),
            'bairros' => Bairro::orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    /**
     * Retorna as sessões do usuário.
     */
    protected function sessions($user)
    {
        if (method_exists($user, 'sessions')) {
            return collect($user->sessions()->get())->map(function ($session) {
                return (object) $session;
            })->all();
        }

        return [];
    }

    /**
     * Exporta todos os dados do cidadão autenticado em formato JSON.
     */
    public function exportData(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('exportProfileData', $user);

        $user->load([
            'solicitacoes.servico',
            'solicitacoes.status',
            'solicitacoes.atendente:id,name',
            'solicitacoes.pesquisa_satisfacao',
        ]);

        $fileName = 'meus_dados_'.now()->format('Y-m-d').'.json';

        return response()->json($user, 200, [
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Anonimiza os dados do cidadão autenticado.
     */
    public function anonymizeAccount(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('anonymizeProfile', $user);

        $user->update([
            'name' => 'Usuário Anônimo #'.$user->id,
            'email' => 'anonymized_'.$user->id.'@'.request()->getHost(),
            'cpf' => null,
            'bairro_id' => null,
            'profile_data' => null,
            'is_active' => false,
        ]);

        $user->syncRoles([]);

        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/')->with('success', 'Sua conta foi anonimizada com sucesso.');
    }

    /**
     * Deleta a conta do usuário autenticado.
     * Esta ação requer uma senha.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('deleteProfile', $user);

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('This password does not match our records.'),
            ]);
        }

        $hasSolicitacoes = SolicitacaoServico::where('user_id', $user->id)->exists();

        if ($hasSolicitacoes) {
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
