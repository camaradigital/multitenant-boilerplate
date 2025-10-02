<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\Tenant\SendCampanhaEmailJob;
use App\Models\Tenant\CampanhaComunicacao;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CampanhaController extends Controller
{
    public function __construct()
    {
        // Garante que apenas usuários com a permissão correta possam acessar
        $this->middleware('can:gerenciar campanhas');
    }

    public function index(Request $request)
    {
        $campanhas = CampanhaComunicacao::with('createdBy')
            ->when($request->input('search'), function ($query, $search) {
                $query->where('titulo', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Tenant/Campanhas/Index', [
            'campanhas' => $campanhas,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        // Obter todos os bairros distintos dos usuários para o filtro
        $bairros = User::whereNotNull('bairro')->distinct()->orderBy('bairro')->pluck('bairro');

        return Inertia::render('Tenant/Campanhas/Create', [
            'bairros' => $bairros,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'segmentacao' => 'required|array',
        ]);

        $campanha = CampanhaComunicacao::create([
            'titulo' => $request->titulo,
            'mensagem' => $request->mensagem,
            'segmentacao' => $request->segmentacao,
            'created_by_user_id' => auth()->id(),
        ]);

        // Disparar o Job para envio em segundo plano
        SendCampanhaEmailJob::dispatch($campanha);

        return redirect()->route('admin.campanhas.index')->with('success', 'Campanha criada e agendada para envio!');
    }

    public function calcularPublico(Request $request)
    {
        $segmentacao = $request->input('segmentacao', []);

        $query = User::query()->whereHas('roles', function ($q) {
            $q->where('name', 'Cidadao'); // Apenas cidadãos
        });

        // Filtrar por Idade
        if (! empty($segmentacao['idade_min'])) {
            $dataNascimentoMax = now()->subYears($segmentacao['idade_min'])->endOfDay();
            $query->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(profile_data, '$.data_nascimento'))"), '<=', $dataNascimentoMax->toDateString());
        }
        if (! empty($segmentacao['idade_max'])) {
            $dataNascimentoMin = now()->subYears($segmentacao['idade_max'] + 1)->startOfDay();
            $query->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(profile_data, '$.data_nascimento'))"), '>=', $dataNascimentoMin->toDateString());
        }

        // Filtrar por Gênero
        if (! empty($segmentacao['genero'])) {
            $query->where(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(profile_data, '$.genero'))"), $segmentacao['genero']);
        }

        // Filtrar por Renda Familiar
        if (! empty($segmentacao['renda_min'])) {
            $query->where(DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(profile_data, '$.renda_familiar')) AS DECIMAL(10,2))"), '>=', (float) $segmentacao['renda_min']);
        }
        if (! empty($segmentacao['renda_max'])) {
            $query->where(DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(profile_data, '$.renda_familiar')) AS DECIMAL(10,2))"), '<=', (float) $segmentacao['renda_max']);
        }

        // Filtrar por Bairro
        if (! empty($segmentacao['bairro'])) {
            $query->where('bairro', $segmentacao['bairro']);
        }

        return response()->json([
            'total' => $query->count(),
        ]);
    }
}
