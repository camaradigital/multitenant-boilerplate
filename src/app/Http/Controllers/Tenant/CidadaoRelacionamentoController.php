<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Tag;
use App\Models\Tenant\User as Cidadao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CidadaoRelacionamentoController extends Controller
{
    /**
     * Display the specified resource.
     * Mostra o "dossiê" completo de um cidadão.
     */
    public function show(Cidadao $cidadao)
    {
        // Verifica se o usuário autenticado tem permissão para visualizar o perfil deste cidadão.
        $this->authorize('viewCidadao', $cidadao);

        $cidadao->load([
            'bairro',
            'solicitacoes.servico',
            'pesquisas_satisfacao',
            'gabineteMessages.respostas.user',
            'candidaturas.vaga',
            'notas.registradoPor',
            'tags',
        ]);

        // KPIs para os cards
        $kpis = [
            'total_solicitacoes' => $cidadao->solicitacoes->count(),
            'mensagens_enviadas' => $cidadao->gabineteMessages->count(),
            'satisfacao_media' => round($cidadao->pesquisas_satisfacao->avg('nota'), 1),
        ];

        // Mapear cada tipo de interação para um formato unificado de timeline
        $solicitacoes = $cidadao->solicitacoes->map(fn ($item) => ['tipo' => 'solicitacao', 'descricao' => "Solicitou o serviço de {$item->servico->nome}", 'id' => $item->id, 'data' => $item->created_at]);
        $feedbacks = $cidadao->pesquisas_satisfacao->map(fn ($item) => ['tipo' => 'feedback', 'descricao' => "Avaliou um serviço com {$item->nota} estrelas.", 'data' => $item->created_at]);
        $mensagens = $cidadao->gabineteMessages->map(fn ($item) => ['tipo' => 'mensagem_gabinete', 'descricao' => "Enviou a mensagem: '{$item->assunto}'", 'id' => $item->id, 'data' => $item->created_at]);
        $candidaturas = $cidadao->candidaturas->map(fn ($item) => ['tipo' => 'candidatura', 'descricao' => "Candidatou-se à vaga de {$item->vaga->titulo}", 'data' => $item->created_at]);
        $notas = $cidadao->notas->map(fn ($item) => ['tipo' => 'nota_interna', 'descricao' => "{$item->registradoPor->name} registrou: '{$item->titulo}'", 'detalhes' => $item->nota, 'data' => $item->created_at]);
        $notifications = $cidadao->notifications->map(fn ($item) => ['tipo' => 'notificacao', 'descricao' => "Recebeu a notificação: '{$item->data['mensagem']}'", 'data' => $item->created_at]);

        // Unir todas as coleções de interações
        $timeline = collect($solicitacoes)
            ->concat($feedbacks)
            ->concat($mensagens)
            ->concat($candidaturas)
            ->concat($notas)
            ->concat($notifications)
            ->sortByDesc('data') // Ordenar pela data, do mais recente para o mais antigo
            ->values(); // Resetar as chaves do array

        return Inertia::render('Tenant/Cidadaos/Show', [
            'cidadao' => $cidadao,
            'kpis' => $kpis,
            'timeline' => $timeline,
            'todasAsTags' => Tag::all(),
        ]);
    }

    /**
     * Store a newly created note for the citizen.
     */
    public function storeNota(Request $request, Cidadao $cidadao)
    {
        // Verifica se o usuário tem permissão para gerenciar notas para este cidadão.
        $this->authorize('manageNotesCidadao', $cidadao);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'nota' => 'required|string',
        ]);

        $cidadao->notas()->create([
            'titulo' => $request->titulo,
            'nota' => $request->nota,
            'registrado_por_user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Nota adicionada com sucesso.');
    }

    /**
     * Attach a tag to the citizen.
     */
    public function attachTag(Request $request, Cidadao $cidadao)
    {
        // Verifica se o usuário tem permissão para gerenciar tags para este cidadão.
        $this->authorize('manageTagsCidadao', $cidadao);

        $request->validate(['tag_id' => 'required|exists:tenant.tags,id']);
        $cidadao->tags()->syncWithoutDetaching($request->tag_id);

        return redirect()->back()->with('success', 'Tag adicionada.');
    }

    /**
     * Detach a tag from the citizen.
     */
    public function detachTag(Cidadao $cidadao, Tag $tag)
    {
        // Verifica se o usuário tem permissão para gerenciar tags para este cidadão.
        $this->authorize('manageTagsCidadao', $cidadao);

        $cidadao->tags()->detach($tag->id);

        return redirect()->back()->with('success', 'Tag removida.');
    }
}
