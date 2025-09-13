<?php

namespace App\Http\Middleware;

use App\Models\Central\Tenant; // Certifique-se que o seu modelo Tenant está correto.
use Illuminate\Http\Request;
use App\Models\CustomField;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;
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
        $currentTenant = Tenant::current();

        // --- CORREÇÃO 1: Carregar a relação 'roles' junto com o usuário ---
        // Usamos '?->load()' para carregar de forma segura a relação de papéis.
        // Isso garante que 'user->roles' estará sempre disponível no frontend.
        $tenantUser = $request->user('tenant')?->load('roles');

        Log::debug('Tenant current no Inertia middleware: ' . ($currentTenant ? 'ID ' . $currentTenant->id : 'null'));

        $parentData = parent::share($request);

        // Sobrescreve os dados do usuário para garantir que o frontend receba
        // as informações corretas do usuário logado no tenant, incluindo suas permissões e papéis.
        if ($tenantUser) {
            $parentData['auth']['user'] = [
                'id' => $tenantUser->id,
                'name' => $tenantUser->name,
                'email' => $tenantUser->email,
                'permissions' => $tenantUser->getAllPermissions()->pluck('name'),
                // --- CORREÇÃO 2: Incluir os papéis carregados no objeto do usuário ---
                'roles' => $tenantUser->roles,
            ];
        }

        return array_merge($parentData, [
            'tenant' => $currentTenant ? $currentTenant->toArray() : null,
            'theme' => [
                'primary'   => $currentTenant?->cor_primaria ?? '#4F46E5',
                'secondary' => $currentTenant?->cor_secundaria ?? '#D946EF',
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ]);
    }
}
