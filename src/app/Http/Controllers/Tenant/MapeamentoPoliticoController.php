<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Bairro;
use App\Models\Tenant\Relatorio;
use App\Models\Tenant\User;
use Inertia\Inertia;

class MapeamentoPoliticoController extends Controller
{
    public function index()
    {
        // Adicionada a autorização usando a RelatorioPolicy
        $this->authorize('viewMapeamentoPolitico', Relatorio::class);

        // 1. Obter a lista de todos os bairros que possuem cidadãos cadastrados
        $bairros = Bairro::whereHas('users', function ($query) {
            $query->role('Cidadao');
        })
            ->orderBy('nome')
            ->get();

        // 2. Para cada bairro, buscar os top 5 cidadãos mais engajados
        $liderancasPorBairro = $bairros->mapWithKeys(function ($bairroModel) {
            $lideres = User::role('Cidadao')
                ->where('bairro_id', $bairroModel->id)
                ->with('tags') // Carrega as tags de cada líder
                ->orderByDesc('engagement_score')
                ->orderBy('name')
                ->limit(5)
                ->get(['id', 'name', 'engagement_score']);

            return [$bairroModel->nome => $lideres];
        });

        return Inertia::render('Tenant/MapeamentoPolitico/Index', [
            'liderancasPorBairro' => $liderancasPorBairro,
        ]);
    }
}
