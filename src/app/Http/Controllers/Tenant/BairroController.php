<?php

// src/app/Http/Controllers/Tenant/BairroController.php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\UpsertBairroRequest;
use App\Models\Tenant\Bairro;
use Illuminate\Http\JsonResponse; // <-- Adicionado
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // <-- Adicionado
use Inertia\Inertia;
use Inertia\Response;

class BairroController extends Controller
{
    public function __construct()
    {
        // Aplica autorização para os métodos padrões do resource (CRUD)
        $this->authorizeResource(Bairro::class, 'bairro');

        // Métodos 'search' e 'approve' precisam de autorização manual,
        // mas 'search' (para registro) provavelmente é público/guest,
        // definido nas rotas. 'approve' deve ser protegido.
    }

    /**
     * Lista os bairros (separando Aprovados e Pendentes)
     * Conforme o "Próximo Passo" do nosso plano.
     */
    public function index(): Response
    {
        // Bairros aprovados (paginados)
        $bairrosAprovados = Bairro::query()
            ->select(['id', 'nome', 'aprovado', 'created_at']) // 'aprovado' é útil no frontend
            ->where('aprovado', true)
            ->orderBy('nome')
            ->paginate(10)
            ->withQueryString(); // Mantém a paginação ao filtrar

        // Bairros pendentes (todos, sem paginação)
        $bairrosPendentes = Bairro::query()
            ->select(['id', 'nome', 'aprovado', 'created_at'])
            ->where('aprovado', false)
            ->orderBy('nome')
            ->get();

        return Inertia::render('Tenant/Bairros/Index', [
            'bairros' => $bairrosAprovados, // Lista principal paginada
            'bairrosPendentes' => $bairrosPendentes, // Lista de pendentes
        ]);
    }

    /**
     * Método de busca (autocomplete) para o formulário de registro.
     * Conforme a Etapa 1.3 do nosso plano.
     */
    public function search(Request $request): JsonResponse
    {
        // Esta rota (ex: /bairros/search) deve ser pública ou 'guest'
        // no seu arquivo de rotas, por isso não usamos authorizeResource.
        
        $term = $request->query('term');

        // Evita busca desnecessária no banco
        if (strlen($term) < 3) {
            return response()->json([]);
        }

        $bairros = Bairro::where('aprovado', true) // <-- SÓ APROVADOS
            ->where('nome', 'LIKE', $term . '%')
            ->select('id', 'nome')
            ->take(10)
            ->get();

        return response()->json($bairros);
    }

    public function create(): Response
    {
        return Inertia::render('Tenant/Bairros/Form');
    }

    public function store(UpsertBairroRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // Garante que o bairro criado pelo Admin já venha aprovado
        $data['aprovado'] = true;

        Bairro::create($data);

        return redirect()->route('admin.bairros.index')
            ->with('success', 'Bairro criado com sucesso.');
    }

    public function edit(Bairro $bairro): Response
    {
        return Inertia::render('Tenant/Bairros/Form', [
            // Enviamos 'aprovado' caso queira exibir/editar esse status no form
            'bairro' => $bairro->only('id', 'nome', 'aprovado'),
        ]);
    }

    public function update(UpsertBairroRequest $request, Bairro $bairro): RedirectResponse
    {
        $bairro->update($request->validated());

        return redirect()->route('admin.bairros.index')
            ->with('success', 'Bairro atualizado com sucesso.');
    }

    public function destroy(Bairro $bairro): RedirectResponse
    {
        // TODO: Adicionar verificação se o bairro possui usuários
        // if ($bairro->users()->exists()) {
        //     return redirect()->route('admin.bairros.index')
        //         ->with('error', 'Não é possível excluir um bairro que possui usuários.');
        // }

        $bairro->delete();

        return redirect()->route('admin.bairros.index')
            ->with('success', 'Bairro excluído com sucesso.');
    }
    
    /**
     * Novo método para aprovar um bairro pendente.
     * Você precisará criar uma rota para ele (ex: POST /bairros/{bairro}/approve)
     */
    public function approve(Bairro $bairro): RedirectResponse
    {
        // Autorização manual (ex: só admin pode aprovar)
        $this->authorize('update', $bairro); // Reutilizando a policy 'update'

        $bairro->update(['aprovado' => true]);

        return redirect()->route('admin.bairros.index')
            ->with('success', 'Bairro aprovado com sucesso.');
    }
}
