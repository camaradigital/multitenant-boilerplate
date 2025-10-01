<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AchadoEPerdidoDocumento;
use App\Models\Tenant\PessoaDesaparecida;
use App\Models\Tenant\TipoServico; // Adicionado para buscar por categoria
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalController extends Controller
{
    /**
     * Exibe a página inicial pública do tenant (Catálogo de Serviços).
     */
    public function home()
    {
        // CORREÇÃO: Busca os serviços agrupados por tipo, conforme esperado pelo frontend.
        // Carrega apenas os tipos de serviço que têm serviços ativos associados.
        $tiposDeServico = TipoServico::where('is_active', true)
            ->whereHas('servicos', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['servicos' => function ($query) {
                $query->where('is_active', true)->orderBy('nome');
            }])
            ->orderBy('nome')
            ->get();

        // CORREÇÃO: Renderiza a view correta ('Tenant/Home') e passa a prop correta ('tiposDeServico').
        return Inertia::render('Tenant/Portal/Home', [
            'tiposDeServico' => $tiposDeServico,
        ]);
    }

    /**
     * Exibe a página pública de consulta de documentos perdidos.
     */
    public function achadosEPerdidos(Request $request)
    {
        // Valida o campo de busca
        $request->validate(['busca' => 'nullable|string|max:255']);

        // Inicia a query buscando apenas documentos que estão aguardando retirada
        $query = AchadoEPerdidoDocumento::where('status', 'Aguardando Retirada');

        // Se houver um termo de busca, filtra pelo nome no documento
        if ($request->filled('busca')) {
            $query->where('nome_completo', 'like', '%'.$request->busca.'%');
        }

        return Inertia::render('Tenant/Portal/Publico/AchadosEPerdidos', [
            'documentos' => $query->latest('data_encontrado')->paginate(12),
            'filtros' => $request->only('busca'), // Para manter o valor no campo de busca
        ]);
    }

    /**
     * Exibe a galeria pública de pessoas desaparecidas.
     */
    public function pessoasDesaparecidas()
    {
        // Busca apenas os registros com status 'Publicado'
        $pessoas = PessoaDesaparecida::where('status', 'Publicado')
            ->latest('data_desaparecimento')
            ->paginate(9);

        return Inertia::render('Tenant/Portal/Publico/PessoasDesaparecidas', [
            'pessoas' => $pessoas,
        ]);
    }
}
