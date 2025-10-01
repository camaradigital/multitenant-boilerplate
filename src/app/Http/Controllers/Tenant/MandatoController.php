<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Legislatura;
use App\Models\Tenant\Mandato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class MandatoController extends Controller
{
    public function store(Request $request, Legislatura $legislatura)
    {
        $validated = $request->validate([
            'politico_id' => 'required|exists:tenant.politicos,id',
            'cargo' => 'required|string|max:100',
        ]);

        $legislatura->mandatos()->create($validated);

        return Redirect::back()->with('success', 'Membro adicionado.');
    }

    public function update(Request $request, Mandato $mandato)
    {
        $validated = $request->validate([
            'cargo' => 'required|string|max:100',
        ]);

        $mandato->update($validated);

        return Redirect::back()->with('success', 'Cargo do membro atualizado.');
    }

    public function destroy(Mandato $mandato)
    {
        $mandato->delete();

        return Redirect::back()->with('success', 'Membro removido.');
    }
}
