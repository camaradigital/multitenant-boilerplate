<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        // Garante que os modelos Role e Permission usem a conexão 'central'
        // Isso é crucial pois este controller opera no contexto do landlord.
        config(['permission.models.role' => \App\Models\Central\Role::class]);
        config(['permission.models.permission' => \App\Models\Central\Permission::class]);
    }

    /**
     * Exibe uma lista dos papéis e permissões.
     */
    public function index()
    {
        return Inertia::render('Central/Roles/Index', [
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all(),
        ]);
    }

    /**
     * Armazena um novo papel no banco de dados.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('central.roles.index')->with('success', 'Papel criado com sucesso.');
    }

    /**
     * Atualiza um papel existente.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,'.$role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('central.roles.index')->with('success', 'Papel atualizado com sucesso.');
    }

    /**
     * Remove um papel do banco de dados.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('central.roles.index')->with('success', 'Papel removido com sucesso.');
    }
}
