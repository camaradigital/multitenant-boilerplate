<?php
namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParametroController extends Controller
{
    /**
     * Mostra a página de configurações do tenant.
     */
    public function index()
    {
        $this->authorize('gerenciar parametros');

        // Passa as configurações atuais do tenant para a view
        return Inertia::render('Tenant/Parametros/Index', [
            'parametros' => tenant()->only('name', 'site_url', 'cor_primaria', 'cor_secundaria', 'logotipo_url')
        ]);
    }

    /**
     * Atualiza as configurações do tenant.
     */
    public function update(Request $request)
    {
        $this->authorize('gerenciar parametros');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'site_url' => 'nullable|url',
            'cor_primaria' => 'required|string|size:7', // Formato #RRGGBB
            'cor_secundaria' => 'required|string|size:7',
            'logotipo_url' => 'nullable|url',
        ]);

        // Atualiza os dados do tenant no banco central
        tenant()->forceFill($validated)->save();

        return redirect()->back()->with('success', 'Parâmetros atualizados com sucesso!');
    }
}
