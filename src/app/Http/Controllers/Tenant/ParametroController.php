<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
// Importa a nova Policy
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ParametroController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe a página de parâmetros do tenant.
     */
    public function index()
    {
        // Usa a Policy para verificar a permissão 'configuracoes.visualizar'
        $this->authorize('manage-tenant-config', $tenant);

        $tenant = Tenant::current();

        return Inertia::render('Tenant/Parametros/Index', [
            'tenant' => $tenant->only(
                'id',
                'name',
                'site_url',
                'cor_primaria',
                'cor_secundaria',
                'logotipo_url',
                'telefone_contato',
                'whatsapp',
                'email_contato',
                'instagram',
                'youtube',
                'permite_cadastro_cidade_externa',
                'limite_renda_juridico',
                'exigir_renda_juridico',
                'publicar_achados_e_perdidos',
                'publicar_pessoas_desaparecidas',
                'publicar_memoria_legislativa',
                'publicar_vagas_emprego',
                'terms_of_service',
                'privacy_policy'
            ),
        ]);
    }

    /**
     * Atualiza os parâmetros do tenant.
     */
    public function update(Request $request)
    {
        // Usa a Policy para verificar a permissão 'configuracoes.atualizar'
        $this->authorize('manage-tenant-config', $tenant);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'site_url' => 'nullable|url',
            'cor_primaria' => 'required|string|size:7',
            'cor_secundaria' => 'required|string|size:7',
            'logotipo' => 'nullable|image|mimes:jpeg,png,svg|max:2048',
            'telefone_contato' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email_contato' => 'nullable|email|max:255',
            'instagram' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'permite_cadastro_cidade_externa' => 'required|boolean',
            'limite_renda_juridico' => 'required|numeric|min:0',
            'exigir_renda_juridico' => 'required|boolean',
            'publicar_achados_e_perdidos' => 'required|boolean',
            'publicar_pessoas_desaparecidas' => 'required|boolean',
            'publicar_memoria_legislativa' => 'required|boolean',
            'publicar_vagas_emprego' => 'required|boolean',
            'terms_of_service' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
        ]);

        $tenant = Tenant::current();

        if ($request->hasFile('logotipo')) {
            if ($tenant->logotipo_url) {
                Storage::disk('public')->delete($tenant->logotipo_url);
            }
            $path = $request->file('logotipo')->store('tenants/'.$tenant->id.'/logos', 'public');
            $tenant->logotipo_url = $path;
        }

        $tenant->name = $validated['name'];
        $tenant->site_url = $validated['site_url'];
        $tenant->cor_primaria = $validated['cor_primaria'];
        $tenant->cor_secundaria = $validated['cor_secundaria'];
        $tenant->permite_cadastro_cidade_externa = $validated['permite_cadastro_cidade_externa'];
        $tenant->limite_renda_juridico = $validated['limite_renda_juridico'];
        $tenant->exigir_renda_juridico = $validated['exigir_renda_juridico'];
        $tenant->publicar_achados_e_perdidos = $validated['publicar_achados_e_perdidos'];
        $tenant->publicar_pessoas_desaparecidas = $validated['publicar_pessoas_desaparecidas'];
        $tenant->publicar_memoria_legislativa = $validated['publicar_memoria_legislativa'];
        $tenant->terms_of_service = $validated['terms_of_service'];
        $tenant->privacy_policy = $validated['privacy_policy'];

        $tenant->save();

        cache()->forget('tenant_settings_'.$tenant->id);

        return Redirect::back()->with('success', 'Parâmetros atualizados com sucesso.');
    }
}
