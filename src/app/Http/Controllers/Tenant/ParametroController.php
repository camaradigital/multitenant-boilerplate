<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ParametroController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('gerenciar parametros');

        $tenant = Tenant::current();

        // CORREÇÃO: Garante que os campos de documentos legais sejam enviados para o frontend.
        return inertia('Tenant/Parametros/Index', [
            'tenant' => $tenant->only(
                'id',
                'name',
                'site_url',
                'cor_primaria',
                'cor_secundaria',
                'logotipo_url',
                'permite_cadastro_cidade_externa',
                'limite_renda_juridico',
                'exigir_renda_juridico',
                'publicar_achados_e_perdidos',
                'publicar_pessoas_desaparecidas',
                'publicar_memoria_legislativa',
                'terms_of_service', // Campo adicionado
                'privacy_policy'    // Campo adicionado
            )
        ]);
    }

    public function update(Request $request)
    {
        $this->authorize('gerenciar parametros');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'site_url' => 'nullable|url',
            'cor_primaria' => 'required|string|size:7',
            'cor_secundaria' => 'required|string|size:7',
            'logotipo_url' => 'nullable|url',
            'permite_cadastro_cidade_externa' => 'required|boolean',
            'limite_renda_juridico' => 'required|numeric|min:0',
            'exigir_renda_juridico' => 'required|boolean',
            'publicar_achados_e_perdidos' => 'required|boolean',
            'publicar_pessoas_desaparecidas' => 'required|boolean',
            'publicar_memoria_legislativa' => 'required|boolean',
            'terms_of_service' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
        ]);

        $tenant = Tenant::current();

        $tenant->name = $validated['name'];
        $tenant->site_url = $validated['site_url'];
        $tenant->cor_primaria = $validated['cor_primaria'];
        $tenant->cor_secundaria = $validated['cor_secundaria'];
        $tenant->logotipo_url = $validated['logotipo_url'];
        $tenant->permite_cadastro_cidade_externa = $validated['permite_cadastro_cidade_externa'];
        $tenant->limite_renda_juridico = $validated['limite_renda_juridico'];
        $tenant->exigir_renda_juridico = $validated['exigir_renda_juridico'];
        $tenant->publicar_achados_e_perdidos = $validated['publicar_achados_e_perdidos'];
        $tenant->publicar_pessoas_desaparecidas = $validated['publicar_pessoas_desaparecidas'];
        $tenant->publicar_memoria_legislativa = $validated['publicar_memoria_legislativa'];
        $tenant->terms_of_service = $validated['terms_of_service'];
        $tenant->privacy_policy = $validated['privacy_policy'];

        $tenant->save();

        return Redirect::route('admin.parametros.index')->with('success', 'Parâmetros atualizados com sucesso.');
    }
}
