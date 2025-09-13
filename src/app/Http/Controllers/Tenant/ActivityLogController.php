<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ActivityLogController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe a página de log de auditoria com filtros.
     */
    public function index(Request $request)
    {
        // Garante que apenas usuários com a permissão correta possam ver os logs.
        $this->authorize('gerenciar parametros');

        // 1. Validação dos filtros recebidos da requisição.
        $filters = $request->validate([
            'busca_usuario' => 'nullable|string|max:255',
            'evento' => 'nullable|string|in:created,updated,deleted',
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim' => 'nullable|date_format:Y-m-d|after_or_equal:data_inicio',
        ]);

        // 2. Inicia a query base de forma eficiente.
        $query = Activity::with(['causer:id,name', 'subject']);

        // 3. Aplica os filtros de forma dinâmica.

        // Filtra pelo nome do usuário que causou o evento.
        $query->when($filters['busca_usuario'] ?? null, function ($q, $busca) {
            $q->whereHas('causer', function ($subQuery) use ($busca) {
                $subQuery->where('name', 'like', "%{$busca}%");
            });
        });

        // Filtra pelo tipo de evento (created, updated, deleted).
        $query->when($filters['evento'] ?? null, function ($q, $evento) {
            $q->where('event', $evento);
        });

        // Filtra pelo período de tempo.
        $query->when(($filters['data_inicio'] ?? null) && ($filters['data_fim'] ?? null), function ($q) use ($filters) {
            $q->whereBetween('created_at', [$filters['data_inicio'] . ' 00:00:00', $filters['data_fim'] . ' 23:59:59']);
        });

        // 4. Pagina os resultados e mantém os filtros na paginação.
        $activities = $query->latest()->paginate(20)->withQueryString();

        return inertia('Tenant/ActivityLog/Index', [
            'activities' => $activities,
            'filtros' => $filters, // Envia os filtros de volta para preencher o formulário.
        ]);
    }
}
