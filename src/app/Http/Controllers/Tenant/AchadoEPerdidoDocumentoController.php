<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AchadoEPerdidoDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class AchadoEPerdidoDocumentoController extends Controller
{
    public function index()
    {
        return inertia('Tenant/AchadosEPerdidos/Documentos/Index', [
            'documentos' => AchadoEPerdidoDocumento::latest()->paginate(15),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_documento' => 'required|string|max:100',
            'nome_completo' => 'required|string|max:255',
            'numero_documento' => 'nullable|string|max:100',
            'data_encontrado' => 'required|date',
            'local_encontrado' => 'required|string|max:255',
            'entregue_por' => 'required|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        AchadoEPerdidoDocumento::create(array_merge($validated, [
            'registrado_por_user_id' => Auth::id(),
        ]));

        return Redirect::route('admin.achados-e-perdidos-documentos.index')->with('success', 'Documento registrado com sucesso.');
    }

    public function update(Request $request, AchadoEPerdidoDocumento $achadosEPerdidosDocumento)
    {
        $validated = $request->validate([
            'status' => 'required|in:Aguardando Retirada,Entregue',
            'data_entrega' => 'required_if:status,Entregue|nullable|date',
            'retirado_por_nome' => 'required_if:status,Entregue|nullable|string|max:255',
            'retirado_por_cpf' => 'required_if:status,Entregue|nullable|string|max:14',
            // Permite atualizar os dados originais também
            'tipo_documento' => 'required|string|max:100',
            'nome_completo' => 'required|string|max:255',
            'numero_documento' => 'nullable|string|max:100',
            'data_encontrado' => 'required|date',
            'local_encontrado' => 'required|string|max:255',
            'entregue_por' => 'required|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'Entregue') {
            $validated['entregue_por_user_id'] = Auth::id();
        }

        $achadosEPerdidosDocumento->update($validated);

        return Redirect::route('admin.achados-e-perdidos-documentos.index')->with('success', 'Registro atualizado com sucesso.');
    }

    public function destroy(AchadoEPerdidoDocumento $achadosEPerdidosDocumento)
    {
        $achadosEPerdidosDocumento->delete();
        return Redirect::route('admin.achados-e-perdidos-documentos.index')->with('success', 'Registro excluído com sucesso.');
    }
}
