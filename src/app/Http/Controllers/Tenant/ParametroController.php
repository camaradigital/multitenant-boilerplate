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
        $currentTenant = tenant();

        // Usa a Policy para verificar a permissão 'configuracoes.visualizar'
        $this->authorize('manage-tenant-config', $currentTenant);

        // Não precisa mais buscar o tenant aqui, já temos em $currentTenant

        return Inertia::render('Tenant/Parametros/Index', [
            'tenant' => $currentTenant->only( // Usa a variável correta
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
        // Obtém o tenant atual ANTES de usá-lo na autorização e validação
        $currentTenant = tenant();

        // Usa a Policy para verificar a permissão 'configuracoes.atualizar'
        $this->authorize('manage-tenant-config', $currentTenant);

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
            'publicar_vagas_emprego' => 'required|boolean', // Faltava esta validação
            'terms_of_service' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
        ]);

        // Não precisa buscar de novo, já temos em $currentTenant

        if ($request->hasFile('logotipo')) {
            if ($currentTenant->logotipo_url) {
                Storage::disk('public')->delete($currentTenant->logotipo_url);
            }
            $path = $request->file('logotipo')->store('tenants/'.$currentTenant->id.'/logos', 'public');
            $currentTenant->logotipo_url = $path;
        }

        // Atualiza os campos usando a variável correta
        $currentTenant->name = $validated['name'];
        $currentTenant->site_url = $validated['site_url'];
        $currentTenant->cor_primaria = $validated['cor_primaria'];
        $currentTenant->cor_secundaria = $validated['cor_secundaria'];
        $currentTenant->telefone_contato = $validated['telefone_contato']; // Adicionado
        $currentTenant->whatsapp = $validated['whatsapp']; // Adicionado
        $currentTenant->email_contato = $validated['email_contato']; // Adicionado
        $currentTenant->instagram = $validated['instagram']; // Adicionado
        $currentTenant->youtube = $validated['youtube']; // Adicionado
        $currentTenant->permite_cadastro_cidade_externa = $validated['permite_cadastro_cidade_externa'];
        $currentTenant->limite_renda_juridico = $validated['limite_renda_juridico'];
        $currentTenant->exigir_renda_juridico = $validated['exigir_renda_juridico'];
        $currentTenant->publicar_achados_e_perdidos = $validated['publicar_achados_e_perdidos'];
        $currentTenant->publicar_pessoas_desaparecidas = $validated['publicar_pessoas_desaparecidas'];
        $currentTenant->publicar_memoria_legislativa = $validated['publicar_memoria_legislativa'];
        $currentTenant->publicar_vagas_emprego = $validated['publicar_vagas_emprego']; // Adicionado
        $currentTenant->terms_of_service = $validated['terms_of_service'];
        $currentTenant->privacy_policy = $validated['privacy_policy'];

        $currentTenant->save();

        cache()->forget('tenant_settings_'.$currentTenant->id);

        return Redirect::back()->with('success', 'Parâmetros atualizados com sucesso.');
    }
}
