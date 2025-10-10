<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    /**
     * Aplica a RolePolicy aos métodos do resource controller.
     * O segundo parâmetro 'rolesPermission' deve corresponder ao nome do parâmetro na definição da rota.
     */
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'rolesPermission');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $guardName = 'tenant';

        return inertia('Tenant/RolesPermissions/Index', [
            'roles' => Role::where('guard_name', $guardName)->with('permissions')->latest()->paginate(10),
            'permissionsDisponiveis' => Permission::where('guard_name', $guardName)->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tenant.roles,name,NULL,id,guard_name,tenant',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:tenant.permissions,id',
        ]);

        $role = Role::create(['guard_name' => 'tenant', 'name' => $validated['name']]);
        $role->givePermissionTo($validated['permissions']);

        return Redirect::route('admin.roles-permissions.index')->with('success', 'Papel criado com sucesso.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $rolesPermission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('tenant.roles', 'name')->where('guard_name', 'tenant')->ignore($rolesPermission->id)],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:tenant.permissions,id',
        ]);

        // Regra de negócio: impede a renomeação de papéis essenciais.
        if (in_array($rolesPermission->getOriginal('name'), ['Admin Tenant', 'Funcionario', 'Cidadao', 'Advogado Coordenador']) && $rolesPermission->getOriginal('name') !== $validated['name']) {
            return Redirect::back()->with('error', 'Este papel é essencial e não pode ser renomeado.');
        }

        $rolesPermission->name = $validated['name'];
        $rolesPermission->save();

        $rolesPermission->syncPermissions($validated['permissions']);

        return Redirect::route('admin.roles-permissions.index')->with('success', 'Papel atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $rolesPermission)
    {
        // A autorização, incluindo a verificação de papel essencial, já foi tratada pela RolePolicy.
        $rolesPermission->delete();

        return Redirect::route('admin.roles-permissions.index')->with('success', 'Papel excluído com sucesso.');
    }
}
