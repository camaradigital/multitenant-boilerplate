<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Legislatura;
use App\Models\Tenant\Mandato;
use App\Models\Tenant\Politico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class MandatoController extends Controller
{
    public function store(Request $request, Legislatura $legislatura)
    {
        $validated = $request->validate([
            'politico_id' => 'required|exists:tenant.politicos,id',
            'cargo' => 'required|string|max:100',
        ]);

        // Validação: Se a legislatura é a atual, o político DEVE ter um e-mail.
        if ($legislatura->is_atual) {
            $politico = Politico::find($validated['politico_id']);
            if (empty($politico->email)) {
                throw ValidationException::withMessages([
                    'politico_id' => 'O político selecionado precisa ter um e-mail cadastrado para ser adicionado à legislatura atual.',
                ]);
            }
        }

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
