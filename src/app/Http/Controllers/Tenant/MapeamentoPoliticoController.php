<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class MapeamentoPoliticoController extends Controller
{
    public function index()
    {
        // 1. Obter a lista de todos os bairros que possuem cidadãos cadastrados
        $bairros = User::role('Cidadao')
            ->whereNotNull('bairro')
            ->where('bairro', '!=', '')
            ->distinct()
            ->pluck('bairro');

        // 2. Para cada bairro, buscar os top 5 cidadãos mais engajados
        $liderancasPorBairro = $bairros->mapWithKeys(function ($bairro) {
            $lideres = User::role('Cidadao')
                ->where('bairro', $bairro)
                ->with('tags') // Carrega as tags de cada líder
                ->orderByDesc('engagement_score')
                ->orderBy('name')
                ->limit(5)
                ->get(['id', 'name', 'engagement_score']);

            return [$bairro => $lideres];
        });

        return Inertia::render('Tenant/MapeamentoPolitico/Index', [
            'liderancasPorBairro' => $liderancasPorBairro,
        ]);
    }
}
