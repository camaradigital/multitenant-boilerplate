<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Documento;
use App\Models\Tenant\SolicitacaoServico;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    use AuthorizesRequests;
    /**
     * Salva um novo documento.
     */
    public function store(Request $request, SolicitacaoServico $solicitacao)
    {
        $request->validate([
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
        ]);

        $file = $request->file('documento');

        // Salva o arquivo no disco privado do tenant
        $path = $file->store('/', 'tenant_private');

        $solicitacao->documentos()->create([
            'user_id' => Auth::id(),
            'nome_original' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'tamanho' => $file->getSize(),
        ]);

        return Redirect::back()->with('success', 'Documento enviado com sucesso.');
    }

    /**
     * Permite o download de um documento.
     */
    public function download(Documento $documento)
    {
        $user = Auth::user();
        $solicitacao = $documento->solicitacao;

        // Autorização: Permite o download se o usuário for o dono da solicitação
        // ou se for um funcionário/admin.
        if ($user->id !== $solicitacao->user_id && !$user->hasRole(['Admin Tenant', 'Funcionario'])) {
            abort(403, 'Ação não autorizada.');
        }

        // Retorna o arquivo para download com o nome original
        return Storage::disk('tenant_private')->download($documento->path, $documento->nome_original);
    }

    /**
     * Exclui um documento.
     */
    public function destroy(Documento $documento)
{
    // Esta linha chama o método 'delete' da sua DocumentoPolicy.
    // Se a policy retornar 'false', o Laravel automaticamente
    // lançará uma exceção de autorização (403 Forbidden).
    $this->authorize('delete', $documento);

    // Deleta o arquivo do disco
    Storage::disk('tenant_private')->delete($documento->path);

    // Deleta o registro do banco de dados
    $documento->delete();

    return Redirect::back()->with('success', 'Documento excluído com sucesso.');
}
}
