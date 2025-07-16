<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Servico;

use Inertia\Inertia;

class PortalController extends Controller {

    public function home() {

        return Inertia::render('Tenant/Portal/Home', [
            'servicos' => Servico::where('is_active', true)->with('tipoServico')->get(),
        ]);
    }
}
