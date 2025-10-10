<?php

namespace App\Http\Controllers\Tenant\PortalCidadao;

use App\Http\Controllers\Controller;
use App\Mail\Tenant\NovaSugestaoProjetoMail;
use App\Models\Tenant\Comissao;
use App\Models\Tenant\Legislatura;
use App\Models\Tenant\Mandato;
use App\Models\Tenant\SugestaoProjetoLei;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PortalPessoalSugestaoController extends Controller
{
    /**
     * Mostra o formulário para criar uma nova sugestão.
     */
    public function create()
    {
        // Garante que o usuário tem permissão para criar uma sugestão
        $this->authorize('create', SugestaoProjetoLei::class);

        $legislaturaAtual = Legislatura::atual()->first();
        $comissoes = $legislaturaAtual ? Comissao::where('legislatura_id', $legislaturaAtual->id)->orderBy('nome')->get(['id', 'nome']) : [];

        return Inertia::render('Tenant/PortalPessoal/IndicacaoProjeto/Create', [
            'comissoes' => $comissoes,
        ]);
    }

    /**
     * Salva a nova sugestão no banco de dados.
     */
    public function store(Request $request)
    {
        // Garante que o usuário tem permissão para criar uma sugestão
        $this->authorize('create', SugestaoProjetoLei::class);

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'area_tematica_id' => 'nullable|exists:tenant.comissoes,id',
            'anexo' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048', // 2MB Max
        ]);

        $user = Auth::user();

        $sugestao = new SugestaoProjetoLei;
        $sugestao->protocolo = 'PRJ-'.now()->timestamp.'-'.Str::upper(Str::random(4));
        $sugestao->cidadao_nome = $user->name;
        $sugestao->cidadao_email = $user->email;
        $sugestao->cidadao_telefone = $user->profile_data['telefone'] ?? null;
        $sugestao->titulo = $validated['titulo'];
        $sugestao->descricao = $validated['descricao'];
        $sugestao->area_tematica_id = $validated['area_tematica_id'];

        if ($request->hasFile('anexo')) {
            $sugestao->anexo_path = $request->file('anexo')->store('sugestoes_anexos', 'public');
        }

        $sugestao->save();

        // Lógica de envio de e-mail
        $this->notificarDestinatarios($sugestao);

        return redirect()->route('portalcidadao.sugestao.success', ['protocolo' => $sugestao->protocolo]);
    }

    /**
     * Mostra a página de sucesso após o envio.
     */
    public function success($protocolo)
    {
        return Inertia::render('Tenant/PortalPessoal/IndicacaoProjeto/Success', [
            'protocolo' => $protocolo,
        ]);
    }

    /**
     * Determina os destinatários e envia o e-mail de notificação.
     */
    private function notificarDestinatarios(SugestaoProjetoLei $sugestao)
    {
        $destinatarios = [];

        if ($sugestao->area_tematica_id) {
            $comissao = Comissao::with('membros.politico')->find($sugestao->area_tematica_id);
            if ($comissao) {
                foreach ($comissao->membros as $membro) {
                    if ($membro->politico && $membro->politico->email) {
                        $destinatarios[] = $membro->politico->email;
                    }
                }
            }
        }

        // Se não houver destinatários da comissão, envia para a mesa diretora
        if (empty($destinatarios)) {
            $legislaturaAtual = Legislatura::atual()->first();
            if ($legislaturaAtual) {
                $cargosMesaDiretora = ['Presidente', 'Vice-Presidente', '1º Secretário', '2º Secretário']; // Exemplo

                $membrosMesa = Mandato::where('legislatura_id', $legislaturaAtual->id)
                    ->whereIn('cargo', $cargosMesaDiretora) // Alterado de 'cargo_mesa_diretora' para 'cargo'
                    ->with('politico')
                    ->get();

                foreach ($membrosMesa as $membro) {
                    if ($membro->politico && $membro->politico->email) {
                        $destinatarios[] = $membro->politico->email;
                    }
                }
            }
        }

        $destinatarios = array_unique(array_filter($destinatarios));

        if (! empty($destinatarios)) {
            Mail::to($destinatarios)->send(new NovaSugestaoProjetoMail($sugestao));
        }
    }
}
