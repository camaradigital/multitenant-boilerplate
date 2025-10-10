<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\CampanhaComunicacaoResource;
use App\Jobs\Tenant\SendCampanhaEmailJob;
use App\Models\Tenant\CampanhaComunicacao;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CampanhaController extends Controller
{
    /**
     * O construtor agora está limpo, a autorização será feita em cada método.
     */
    public function __construct()
    {
        // A autorização é tratada por método individualmente.
    }

    public function index(Request $request)
    {
        // Autorização com a Policy para listar campanhas
        $this->authorize('viewAny', CampanhaComunicacao::class);

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

    public function show(CampanhaComunicacao $campanha)
    {
        // Autorização com a Policy para ver uma campanha específica
        $this->authorize('view', $campanha);

        // Carrega o relacionamento, se não for carregado por padrão
        $campanha->load('createdBy');

        return inertia('Tenant/Campanhas/Show', [
            'campanha' => CampanhaComunicacaoResource::make($campanha),
        ]);
    }

    public function create()
    {
        // Autorização com a Policy para criar uma nova campanha
        $this->authorize('create', CampanhaComunicacao::class);

        // Obter todos os bairros distintos dos usuários para o filtro
        $bairros = User::whereNotNull('bairro')->distinct()->orderBy('bairro')->pluck('bairro');

        return Inertia::render('Tenant/Campanhas/Create', [
            'bairros' => $bairros,
        ]);
    }

    public function store(Request $request)
    {
        // Autorização com a Policy antes de salvar
        $this->authorize('create', CampanhaComunicacao::class);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'mensagem' => 'required|string',
            'segmentacao' => 'required|array',
        ]);

        CampanhaComunicacao::create([
            'titulo' => $request->titulo,
            'mensagem' => $request->mensagem,
            'segmentacao' => $request->segmentacao,
            'created_by_user_id' => auth()->id(),
            'status' => 'Rascunho', // Status inicial
        ]);

        return redirect()->route('admin.campanhas.index')->with('success', 'Campanha criada como rascunho com sucesso!');
    }

    /**
     * Envia a campanha para os destinatários.
     */
    public function send(CampanhaComunicacao $campanha)
    {
        $this->authorize('send', $campanha);

        SendCampanhaEmailJob::dispatch($campanha);

        $campanha->update(['status' => 'Enviando']);

        return back()->with('success', 'A campanha foi agendada para envio!');
    }

    public function calcularPublico(Request $request)
    {
        // A lógica para calcular o público está atrelada à permissão de criar a campanha
        $this->authorize('create', CampanhaComunicacao::class);

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
