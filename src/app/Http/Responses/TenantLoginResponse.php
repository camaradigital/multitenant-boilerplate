<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class TenantLoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): Response
    {
        // 1. Verifica se há um tenant ativo na requisição
        if ($tenant = Tenant::current()) {
            // Se SIM, o usuário está logando em um subdomínio (ex: cmsm.cacsystem.test)
            $request->session()->put('tenant_id', $tenant->id);

            // Redireciona para o dashboard do TENANT, ignorando a configuração 'home' do Fortify
            return $request->wantsJson()
                        ? new JsonResponse('', 204)
                        : redirect()->intended(route('tenant.dashboard'));
        }

        // 2. Se NÃO houver tenant ativo, o usuário está no domínio central (Super Admin)
        // Redireciona usando a configuração 'home' do Fortify, que deve ser '/superadmin/dashboard'
        return $request->wantsJson()
                    ? new JsonResponse('', 204)
                    : redirect()->intended(config('fortify.home'));
    }
}
