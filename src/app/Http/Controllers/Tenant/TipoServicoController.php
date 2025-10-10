<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TipoServico;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class TipoServicoController extends Controller
{
    /**
     * Aplica a TipoServicoPolicy aos métodos do resource controller.
     */
    public function __construct()
    {
        $this->authorizeResource(TipoServico::class, 'tipoServico');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia('Tenant/TiposServico/Index', [
            'tiposServico' => TipoServico::latest()->paginate(10),
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
            'nome' => 'required|string|max:255|unique:tenant.tipos_servico,nome,'.$tipoServico->id,
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
        // A autorização e as regras de negócio de exclusão agora são tratadas pela TipoServicoPolicy.
        try {
            $tipoServico->delete();

            return Redirect::back()->with('success', 'Tipo de serviço excluído com sucesso.');
        } catch (QueryException $e) {
            return Redirect::back()->with('error', 'Erro de banco de dados. Este item pode estar em uso em outra parte do sistema.');
        } catch (Exception $e) {
            return Redirect::back()->with('error', 'Um erro inesperado ocorreu ao tentar excluir.');
        }
    }
}
