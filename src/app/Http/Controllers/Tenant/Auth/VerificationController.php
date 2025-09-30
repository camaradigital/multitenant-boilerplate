<?php

namespace App\Http\Controllers\Tenant\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class VerificationController extends Controller
{
    /**
     * Mostra a tela de aviso de verificação de e-mail.
     */
    public function show(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
                        ? redirect()->route('tenant.dashboard')
                        : Inertia::render('Auth/VerifyEmail');
    }

    /**
     * Marca o e-mail do usuário autenticado como verificado.
     */
    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if (! $user) {
            abort(404);
        }

        if (! URL::hasValidSignature($request)) {
            return redirect()->route('login')->with('error', 'Link de verificação inválido ou expirado.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('tenant.dashboard')->with('status', 'E-mail já verificado!');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Tenta logar o usuário após a verificação
        auth('tenant')->login($user);

        return redirect()->route('tenant.dashboard')->with('status', 'E-mail verificado com sucesso!');
    }


    /**
     * Reenvia a notificação de verificação de e-mail.
     */
    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', 'Seu e-mail já foi verificado.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Um novo link de verificação foi enviado para o seu e-mail.');
    }
}
