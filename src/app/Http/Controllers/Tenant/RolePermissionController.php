<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Permission; // CORREÇÃO: Usar o model personalizado
use App\Models\Tenant\Role;       // CORREÇÃO: Usar o model personalizado
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class RolePermissionController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('gerenciar parametros');

        $guardName = 'tenant';

        return inertia('Tenant/RolesPermissions/Index', [
            'roles' => Role::where('guard_name', $guardName)->with('permissions')->latest()->paginate(10),
            'permissionsDisponiveis' => Permission::where('guard_name', $guardName)->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('gerenciar parametros');

        $validated = $request->validate([
            // Validação aprimorada para garantir unicidade dentro do guard 'tenant'
            'name' => 'required|string|max:50|unique:tenant.roles,name,NULL,id,guard_name,tenant',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:tenant.permissions,id',
        ]);

        $role = Role::create(['guard_name' => 'tenant', 'name' => $validated['name']]);
        $role->givePermissionTo($validated['permissions']);

        return Redirect::route('admin.roles-permissions.index')->with('success', 'Papel criado com sucesso.');
    }

    public function update(Request $request, Role $rolesPermission)
    {
        $this->authorize('gerenciar parametros');

        $validated = $request->validate([
            // Validação aprimorada para ignorar o ID atual dentro do guard 'tenant'
            'name' => ['required', 'string', 'max:50', Rule::unique('tenant.roles', 'name')->where('guard_name', 'tenant')->ignore($rolesPermission->id)],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:tenant.permissions,id',
        ]);

        $rolesPermission->name = $validated['name'];
        $rolesPermission->save();

        $rolesPermission->syncPermissions($validated['permissions']);

        return Redirect::route('admin.roles-permissions.index')->with('success', 'Papel atualizado com sucesso.');
    }

    public function destroy(Role $rolesPermission)
    {
        $this->authorize('gerenciar parametros');

        if (in_array($rolesPermission->name, ['Admin Tenant', 'Funcionario', 'Cidadao'])) {
            return Redirect::back()->with('error', 'Este papel é essencial e não pode ser excluído.');
        }

        $rolesPermission->delete();

        return Redirect::route('admin.roles-permissions.index')->with('success', 'Papel excluído com sucesso.');
    }
}
