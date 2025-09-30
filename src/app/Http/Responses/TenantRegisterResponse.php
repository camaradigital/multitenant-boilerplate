<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TenantRegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function toResponse($request): RedirectResponse
    {
        // Após o registro, o usuário é autenticado.
        // Verificamos se o modelo dele exige a verificação de e-mail e se o e-mail ainda não foi verificado.
        if ($request->user() instanceof MustVerifyEmail && ! $request->user()->hasVerifiedEmail()) {
            // Se as condições forem atendidas, redirecionamos para a nossa rota de aviso de verificação,
            // que já está ciente do tenant.
            return redirect()->route('verification.notice');
        }

        // Caso contrário (se a verificação não for necessária), redirecionamos para o painel principal do tenant.
        return redirect()->route('tenant.dashboard');
    }
}

