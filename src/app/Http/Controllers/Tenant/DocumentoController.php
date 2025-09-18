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

        // 1. Autorização via Policy (mais limpo e centralizado)
        $this->authorize('create', [Documento::class, $solicitacao]);

        $request->validate([
            'documento' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB max
        ]);

        $file = $request->file('documento');

        // 2. CORREÇÃO FINAL: Salva no disco correto E em uma pasta organizada.
        $path = $file->store("solicitacoes/{$solicitacao->id}", 'tenant_private');

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
        // 1. Autorização via Policy (substitui o bloco 'if')
        $this->authorize('view', $documento);

        // Retorna o arquivo para download com o nome original
        return Storage::disk('tenant_private')->download($documento->path, $documento->nome_original);
    }

    /**
     * Exclui um documento.
     */
    public function destroy(Documento $documento)
    {
        // 1. Autorização via Policy (já estava correto)
        $this->authorize('delete', $documento);

        // Deleta o arquivo do disco
        Storage::disk('tenant_private')->delete($documento->path);

        // Deleta o registro do banco de dados
        $documento->delete();

        return Redirect::back()->with('success', 'Documento excluído com sucesso.');
    }
}
