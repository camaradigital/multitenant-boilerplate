<?php

namespace App\Http\Controllers\Central;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Central\ContactFormSubmitted;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validação dos dados (com mensagens em português)
        $validatedData = $request->validate([
            'cityCouncilName' => 'required|string|max:255',
            'contactPerson'   => 'required|string|max:255',
            'email'           => 'required|email|max:255',
            'message'         => 'required|string|max:5000',
        ]);

        // 2. Envio do e-mail
        // Troque 'seu-email-admin@dominio.com' pelo e-mail que deve receber a notificação
        try {
            Mail::to('jmsolucoes@zoho.com')->send(new ContactFormSubmitted($validatedData)); // MUDAR EMAIL DEPOIS
        } catch (\Exception $e) {
            // Se o envio falhar, retorna com um erro
            return back()->with('error', 'Não foi possível enviar sua mensagem. Tente novamente mais tarde.');
        }

        // 3. Resposta de sucesso
        return back()->with('success', 'Contato enviado com sucesso! Nossa equipe retornará em breve.');
    }
}
