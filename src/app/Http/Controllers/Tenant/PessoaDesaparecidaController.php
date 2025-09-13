<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PessoaDesaparecida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Spatie\Multitenancy\Models\Tenant; // Adicionar este import

class PessoaDesaparecidaController extends Controller
{
    public function index()
    {
        return inertia('Tenant/AchadosEPerdidos/PessoasDesaparecidas/Index', [
            'pessoas' => PessoaDesaparecida::with('registradoPor')->latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_completo' => 'required|string|max:255',
            'idade' => 'required|integer|min:0',
            'data_desaparecimento' => 'required|date',
            'local_desaparecimento' => 'required|string|max:255',
            'detalhes' => 'required|string',
            'foto' => 'required|image|max:2048', // 2MB max
            'boletim_ocorrencia' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // --- AQUI ESTÁ A CORREÇÃO ---
        $tenantId = Tenant::current()->id;

        // Salva os arquivos no disco 'public', dentro de uma pasta específica do tenant.
        $fotoPath = $request->file('foto')->store("tenants/{$tenantId}/pessoas_desaparecidas/fotos", 'public');
        $boPath = $request->file('boletim_ocorrencia')->store("tenants/{$tenantId}/pessoas_desaparecidas/boletins", 'public');

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

    public function update(Request $request, PessoaDesaparecida $pessoaDesaparecida)
    {
        $validated = $request->validate([
            'status' => 'required|in:Aguardando Aprovação,Publicado,Encontrado',
        ]);

        $pessoaDesaparecida->update([
            'status' => $validated['status'],
            'moderado_por_user_id' => Auth::id(),
        ]);

        return Redirect::route('admin.pessoas-desaparecidas.index')->with('success', 'Status do registro atualizado.');
    }

    public function destroy(PessoaDesaparecida $pessoaDesaparecida)
    {
        // Deleta os arquivos do disco público antes de deletar o registro
        Storage::disk('public')->delete($pessoaDesaparecida->foto_path);
        Storage::disk('public')->delete($pessoaDesaparecida->boletim_ocorrencia_path);

        $pessoaDesaparecida->delete();

        return Redirect::route('admin.pessoas-desaparecidas.index')->with('success', 'Registro excluído com sucesso.');
    }
}
