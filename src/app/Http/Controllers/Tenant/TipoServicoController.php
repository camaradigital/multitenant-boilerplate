<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TipoServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\QueryException; // Importar exceção do DB
use Exception; // Importar exceção genérica

class TipoServicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Tenant/TiposServico/Index', [
            'tiposServico' => TipoServico::latest()->paginate(10)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:tenant.tipos_servico,nome',
            'descricao' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        TipoServico::create($request->all());

        return Redirect::route('admin.tipos-servico.index')
                         ->with('success', 'Tipo de serviço criado com sucesso.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TipoServico $tipoServico)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:tenant.tipos_servico,nome,' . $tipoServico->id,
            'descricao' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $tipoServico->update($request->all());

        return Redirect::route('admin.tipos-servico.index')
                         ->with('success', 'Tipo de serviço atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TipoServico $tipoServico)
    {
        // 1. Verificação de dependências
        if ($tipoServico->servicos()->exists()) {
            return Redirect::back()->with('error', 'Não é possível excluir. Existem serviços associados a este tipo.');
        }

        try {
            // 2. Tenta deletar e verifica o resultado
            if ($tipoServico->delete()) {
                // Sucesso: a exclusão funcionou
                return Redirect::back()->with('success', 'Tipo de serviço excluído com sucesso.');
            } else {
                // Falha silenciosa: um evento pode ter interrompido a exclusão
                return Redirect::back()->with('error', 'A exclusão foi interrompida por um evento no sistema.');
            }
        } catch (QueryException $e) {
            // 3. Captura erros específicos do banco de dados (ex: Foreign Key)
            // report($e); // Descomente para logar o erro
            return Redirect::back()->with('error', 'Erro de banco de dados. Este item pode estar em uso em outra parte do sistema.');
        } catch (Exception $e) {
            // 4. Captura qualquer outro erro inesperado
            // report($e); // Descomente para logar o erro
            return Redirect::back()->with('error', 'Um erro inesperado ocorreu ao tentar excluir.');
        }
    }
}

