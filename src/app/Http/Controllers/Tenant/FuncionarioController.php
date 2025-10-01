<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\FuncionarioRequest;
use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class FuncionarioController extends Controller
{
    use AuthorizesRequests;

    /**
     * Exibe a lista de funcionários.
     */
    public function index()
    {
        $this->authorize('viewAnyFuncionario', User::class);

        return inertia('Tenant/Funcionarios/Index', [
            'funcionarios' => User::whereHas('roles', fn ($q) => $q->where('name', '!=', 'Cidadao'))
                ->with('roles:id,name')
                ->latest()
                ->paginate(10),
            'rolesDisponiveis' => Role::where('guard_name', 'tenant')
                ->where('name', '!=', 'Cidadao')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Salva um novo funcionário.
     */
    public function store(FuncionarioRequest $request)
    {
        $this->authorize('createFuncionario', User::class);

        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'is_active' => $validatedData['is_active'],
        ]);

        $user->assignRole($validatedData['roles']);

        return Redirect::route('admin.funcionarios.index')->with('success', 'Funcionário criado com sucesso.');
    }

    /**
     * Atualiza um funcionário existente.
     */
    public function update(FuncionarioRequest $request, User $funcionario)
    {
        $this->authorize('updateFuncionario', $funcionario);

        $validatedData = $request->validated();

        $dataToUpdate = [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'is_active' => $validatedData['is_active'],
        ];

        if (! empty($validatedData['password'])) {
            $dataToUpdate['password'] = Hash::make($validatedData['password']);
        }

        $funcionario->update($dataToUpdate);
        $funcionario->syncRoles($validatedData['roles']);

        return Redirect::route('admin.funcionarios.index')->with('success', 'Funcionário atualizado com sucesso.');
    }

    /**
     * Remove um funcionário.
     */
    public function destroy(User $funcionario)
    {
        $this->authorize('deleteFuncionario', $funcionario);

        // A lógica que impedia a auto-exclusão foi movida para a UserPolicy.

        $funcionario->delete();

        return Redirect::route('admin.funcionarios.index')->with('success', 'Funcionário excluído com sucesso.');
    }
}
