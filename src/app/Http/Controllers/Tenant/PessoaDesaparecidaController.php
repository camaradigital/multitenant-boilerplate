<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PessoaDesaparecida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Tenant;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PessoaDesaparecidaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $this->authorize('viewAny', PessoaDesaparecida::class);

        return inertia('Tenant/AchadosEPerdidos/PessoasDesaparecidas/Index', [
            'pessoas' => PessoaDesaparecida::with('registradoPor')->latest()->paginate(10),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', PessoaDesaparecida::class);

        $validated = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'idade' => 'required|integer|min:0',
            'data_desaparecimento' => 'required|date',
            'local_desaparecimento' => 'required|string|max:255',
            'detalhes' => 'required|string',
            'foto' => 'required|image|max:2048', // 2MB max
            'boletim_ocorrencia' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $tenantId = Tenant::current()->id;

        // A foto é pública para ser exibida no portal.
        $fotoPath = $request->file('foto')->store("tenants/{$tenantId}/pessoas_desaparecidas/fotos", 'public');
        // O B.O. é privado e salvo no disco padrão (tenant_private).
        $boPath = $request->file('boletim_ocorrencia')->store("tenants/{$tenantId}/pessoas_desaparecidas/boletins");

        PessoaDesaparecida::create([
            'nome_completo' => $validated['nome_completo'],
            'idade' => $validated['idade'],
            'data_desaparecimento' => $validated['data_desaparecimento'],
            'local_desaparecimento' => $validated['local_desaparecimento'],
            'detalhes' => $validated['detalhes'],
            'foto_path' => $fotoPath,
            'boletim_ocorrencia_path' => $boPath,
            'registrado_por_user_id' => Auth::id(),
        ]);

        return Redirect::route('admin.pessoas-desaparecidas.index')->with('success', 'Registro enviado para moderação.');
    }

    /**
     * Update the status of the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, PessoaDesaparecida $pessoaDesaparecida)
    {
        // AJUSTE: Usando a autorização 'moderate' que é mais específica para esta ação.
        $this->authorize('moderate', $pessoaDesaparecida);

        $validated = $request->validate([
            'status' => 'required|in:Aguardando Aprovação,Publicado,Encontrado',
        ]);

        $pessoaDesaparecida->update([
            'status' => $validated['status'],
            'moderado_por_user_id' => Auth::id(),
        ]);

        return Redirect::route('admin.pessoas-desaparecidas.index')->with('success', 'Status do registro atualizado.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(PessoaDesaparecida $pessoaDesaparecida)
    {
        $this->authorize('delete', $pessoaDesaparecida);

        // Deleta os arquivos do disco antes de deletar o registro
        Storage::disk('public')->delete($pessoaDesaparecida->foto_path);
        Storage::delete($pessoaDesaparecida->boletim_ocorrencia_path);

        $pessoaDesaparecida->delete();

        return Redirect::route('admin.pessoas-desaparecidas.index')->with('success', 'Registro excluído com sucesso.');
    }

    /**
     * Download the police report for the specified resource.
     */
    public function downloadBoletim(PessoaDesaparecida $pessoaDesaparecida): StreamedResponse
    {
        $this->authorize('viewBoletim', $pessoaDesaparecida);

        abort_if(
            ! Storage::exists($pessoaDesaparecida->boletim_ocorrencia_path),
            404,
            'Arquivo não encontrado.'
        );

        return Storage::download($pessoaDesaparecida->boletim_ocorrencia_path);
    }
}
