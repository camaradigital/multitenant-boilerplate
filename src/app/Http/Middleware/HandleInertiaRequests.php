<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Multitenancy\Models\Tenant; // Usando o model padrão do Spatie para melhor portabilidade.
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // --- LÓGICA CORRIGIDA E SIMPLIFICADA ---
        // Graças à nossa SwitchAuthConfigTask, $request->user() agora retorna
        // o usuário correto (seja do tenant ou central) automaticamente.
        $user = $request->user();

        // Garante que os papéis do usuário sejam sempre carregados, se ele existir.
        $user?->load('roles');

        $currentTenant = Tenant::current();

        return [
            ...parent::share($request),

            'auth' => [
                // Se um usuário estiver logado, compartilha seus dados.
                // Caso contrário, compartilha 'null', como esperado pelo Inertia.
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // Garante que só tentemos pegar permissões se o usuário for de um tenant.
                    'permissions' => $user instanceof \App\Models\Tenant\User ? $user->getAllPermissions()->pluck('name') : [],
                    'roles' => $user->roles,
                ] : null,
            ],

            // Usar toArray() é mais simples e pega todos os atributos fillable,
            // garantindo que os novos campos de contato sejam incluídos.
            'tenant' => $currentTenant ? $currentTenant->toArray() : null,

            'theme' => [
                'primary'   => $currentTenant?->cor_primaria ?? '#4F46E5',
                'secondary' => $currentTenant?->cor_secundaria ?? '#D946EF',
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'status'  => fn () => $request->session()->get('status'),
            ],

            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}

